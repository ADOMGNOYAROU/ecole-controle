import { Injectable, Logger } from '@nestjs/common';
import { ConfigService } from '@nestjs/config';
import { createTransport, type Transporter } from 'nodemailer';

export interface RappelPaiementEmail {
  destinataire: string;
  eleveNom: string;
  type: string;
  dateEcheance: string;
  solde: number;
  enRetard: boolean;
}

@Injectable()
export class MailService {
  private readonly logger = new Logger(MailService.name);
  private readonly transporteur: Transporter | null;
  private readonly expediteur: string;

  constructor(private readonly config: ConfigService) {
    const host = this.config.get<string>('MAIL_HOST');
    this.expediteur =
      this.config.get<string>('MAIL_FROM') ?? 'no-reply@scolarix.app';

    if (!host) {
      this.logger.warn(
        'MAIL_HOST non configuré : les emails seront journalisés mais pas envoyés.',
      );
      this.transporteur = null;
      return;
    }

    this.transporteur = createTransport({
      host,
      port: Number(this.config.get<string>('MAIL_PORT') ?? '587'),
      secure: this.config.get<string>('MAIL_SECURE') === 'true',
      auth: this.config.get<string>('MAIL_USER')
        ? {
            user: this.config.get<string>('MAIL_USER'),
            pass: this.config.get<string>('MAIL_PASSWORD'),
          }
        : undefined,
    });
  }

  async envoyerRappelPaiement(donnees: RappelPaiementEmail): Promise<void> {
    const sujet = donnees.enRetard
      ? `Paiement en retard — ${donnees.eleveNom}`
      : `Rappel : échéance de paiement à venir — ${donnees.eleveNom}`;

    const html = this.gabaritRappelPaiement(donnees);

    if (!this.transporteur) {
      this.logger.log(
        `[email non envoyé — MAIL_HOST absent] À ${donnees.destinataire} : ${sujet}`,
      );
      return;
    }

    try {
      await this.transporteur.sendMail({
        from: this.expediteur,
        to: donnees.destinataire,
        subject: sujet,
        html,
      });
    } catch (error) {
      this.logger.error(
        `Échec d'envoi du rappel de paiement à ${donnees.destinataire}`,
        error as Error,
      );
    }
  }

  private gabaritRappelPaiement(d: RappelPaiementEmail): string {
    const solde = new Intl.NumberFormat('fr-FR').format(d.solde);
    const message = d.enRetard
      ? `Le paiement de <strong>${d.eleveNom}</strong> (${d.type}) est <strong style="color:#dc2626;">en retard</strong> depuis le ${d.dateEcheance}.`
      : `Le paiement de <strong>${d.eleveNom}</strong> (${d.type}) arrive à échéance le <strong>${d.dateEcheance}</strong>.`;

    return `
      <!DOCTYPE html>
      <html lang="fr"><head><meta charset="UTF-8"></head>
      <body style="font-family: Arial, sans-serif; background:#f1f5f9; padding:24px; margin:0;">
        <table role="presentation" width="100%" style="max-width:480px; margin:0 auto; background:#ffffff; border-radius:12px; overflow:hidden;">
          <tr><td style="background:#4338ca; padding:20px 24px; color:#ffffff; font-size:18px; font-weight:bold;">Scolarix</td></tr>
          <tr><td style="padding:24px; color:#1e293b;">
            <p style="margin:0 0 12px;">Bonjour,</p>
            <p style="margin:0 0 12px;">${message}</p>
            <p style="margin:0 0 12px;">Montant restant dû : <strong>${solde} FCFA</strong></p>
            <p style="margin:20px 0 0; font-size:13px; color:#64748b;">Merci de régulariser cette échéance auprès de l'administration de l'école.</p>
          </td></tr>
        </table>
      </body></html>
    `;
  }
}
