import { Injectable } from '@nestjs/common';
import PDFDocument from 'pdfkit';

export interface RapportListeOptions {
  titre: string;
  colonnes: string[];
  lignes: string[][];
  sousTitre?: string;
}

export interface MatiereBulletin {
  nom: string;
  moyenne: number | null;
  coefficient: number;
}

export interface RapportBulletinOptions {
  eleveNom: string;
  eleveMatricule: string;
  classeNom: string;
  trimestreNom: string;
  anneeScolaireLibelle: string;
  matieres: MatiereBulletin[];
  moyenneGenerale: number | null;
  tauxPresence: number | null;
  rang: number | null;
  appreciation: string;
}

@Injectable()
export class PdfService {
  genererListePdf(options: RapportListeOptions): Promise<Buffer> {
    const paysage = options.colonnes.length > 5;
    const doc = new PDFDocument({
      size: 'A4',
      layout: paysage ? 'landscape' : 'portrait',
      margin: 40,
    });

    doc
      .font('Helvetica-Bold')
      .fontSize(16)
      .text(options.titre, { align: 'center' });
    if (options.sousTitre) {
      doc
        .moveDown(0.3)
        .font('Helvetica')
        .fontSize(10)
        .fillColor('#6b7280')
        .text(options.sousTitre, {
          align: 'center',
        });
    }
    doc
      .moveDown(0.3)
      .font('Helvetica')
      .fontSize(9)
      .fillColor('#9ca3af')
      .text(`Généré le ${new Date().toLocaleDateString('fr-FR')}`, {
        align: 'center',
      });
    doc.moveDown(1).fillColor('#000');

    const largeurPage =
      doc.page.width - doc.page.margins.left - doc.page.margins.right;
    const largeurColonne = largeurPage / options.colonnes.length;

    this.dessinerLigne(doc, options.colonnes, largeurColonne, true);
    for (const ligne of options.lignes) {
      this.sautDePageSiNecessaire(doc);
      this.dessinerLigne(doc, ligne, largeurColonne, false);
    }
    if (options.lignes.length === 0) {
      doc
        .font('Helvetica')
        .fontSize(10)
        .fillColor('#6b7280')
        .text('Aucune donnée.');
    }

    return this.finaliser(doc);
  }

  genererBulletinPdf(options: RapportBulletinOptions): Promise<Buffer> {
    const doc = new PDFDocument({ size: 'A4', margin: 50 });

    doc
      .font('Helvetica-Bold')
      .fontSize(16)
      .text(`Bulletin scolaire — ${options.trimestreNom}`, { align: 'center' });
    doc
      .moveDown(0.3)
      .font('Helvetica')
      .fontSize(10)
      .fillColor('#6b7280')
      .text(options.anneeScolaireLibelle, { align: 'center' });
    doc.moveDown(1).fillColor('#000').fontSize(11);

    doc.text(
      `Élève : ${options.eleveNom}     Matricule : ${options.eleveMatricule}`,
    );
    const taux =
      options.tauxPresence !== null ? `${options.tauxPresence}%` : 'N/A';
    doc.text(`Classe : ${options.classeNom}     Taux de présence : ${taux}`);
    doc.moveDown(1);

    const largeurPage =
      doc.page.width - doc.page.margins.left - doc.page.margins.right;
    const largeursColonnes = [
      largeurPage * 0.5,
      largeurPage * 0.25,
      largeurPage * 0.25,
    ];
    const x0 = doc.page.margins.left;

    this.dessinerLigneBulletin(
      doc,
      x0,
      ['Matière', 'Moyenne /20', 'Coefficient'],
      largeursColonnes,
      true,
    );

    if (options.matieres.length === 0) {
      doc
        .font('Helvetica')
        .fontSize(10)
        .text('Aucune note enregistrée pour ce trimestre.');
    } else {
      for (const matiere of options.matieres) {
        this.dessinerLigneBulletin(
          doc,
          x0,
          [
            matiere.nom,
            matiere.moyenne !== null ? String(matiere.moyenne) : '—',
            String(matiere.coefficient),
          ],
          largeursColonnes,
          false,
        );
      }
    }

    doc.moveDown(1.5).font('Helvetica-Bold').fontSize(12);
    doc.text(
      `Moyenne générale : ${options.moyenneGenerale !== null ? `${options.moyenneGenerale}/20` : 'N/A'}`,
    );
    doc.text(`Rang : ${options.rang ?? 'N/A'}`);
    doc.text(`Appréciation : ${options.appreciation}`);

    doc
      .moveDown(2)
      .font('Helvetica')
      .fontSize(9)
      .fillColor('#6b7280')
      .text(`Bulletin généré le ${new Date().toLocaleString('fr-FR')}`, {
        align: 'right',
      });

    return this.finaliser(doc);
  }

  private dessinerLigneBulletin(
    doc: PDFKit.PDFDocument,
    x0: number,
    cellules: string[],
    largeursColonnes: number[],
    entete: boolean,
  ): void {
    doc.font(entete ? 'Helvetica-Bold' : 'Helvetica').fontSize(10);
    const y = doc.y;
    let x = x0;
    cellules.forEach((cellule, i) => {
      doc.text(cellule, x, y, { width: largeursColonnes[i] });
      x += largeursColonnes[i];
    });
    doc.moveDown(0.6);
    if (entete) {
      const largeurTotale = largeursColonnes.reduce((a, b) => a + b, 0);
      doc
        .moveTo(x0, doc.y)
        .lineTo(x0 + largeurTotale, doc.y)
        .strokeColor('#d1d5db')
        .stroke();
      doc.moveDown(0.3);
    }
  }

  private dessinerLigne(
    doc: PDFKit.PDFDocument,
    cellules: string[],
    largeurColonne: number,
    entete: boolean,
  ): void {
    const x0 = doc.page.margins.left;
    const y = doc.y;
    doc.font(entete ? 'Helvetica-Bold' : 'Helvetica').fontSize(9);
    cellules.forEach((cellule, i) => {
      doc.text(cellule, x0 + i * largeurColonne, y, {
        width: largeurColonne,
        ellipsis: true,
      });
    });
    doc.moveDown(0.7);
    if (entete) {
      doc
        .moveTo(x0, doc.y)
        .lineTo(doc.page.width - doc.page.margins.right, doc.y)
        .strokeColor('#d1d5db')
        .stroke();
      doc.moveDown(0.3);
    }
  }

  private sautDePageSiNecessaire(doc: PDFKit.PDFDocument): void {
    if (doc.y > doc.page.height - doc.page.margins.bottom - 30) {
      doc.addPage();
    }
  }

  private finaliser(doc: PDFKit.PDFDocument): Promise<Buffer> {
    return new Promise((resolve, reject) => {
      const morceaux: Buffer[] = [];
      doc.on('data', (chunk: Buffer) => morceaux.push(chunk));
      doc.on('end', () => resolve(Buffer.concat(morceaux)));
      doc.on('error', reject);
      doc.end();
    });
  }
}
