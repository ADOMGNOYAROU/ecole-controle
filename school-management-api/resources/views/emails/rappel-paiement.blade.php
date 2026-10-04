@php
    $eleve = $paiement->eleve;
    $solde = $paiement->solde();
    $enRetard = $paiement->estEnRetard();
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; background:#f1f5f9; padding:24px; margin:0;">
    <table role="presentation" width="100%" style="max-width:480px; margin:0 auto; background:#ffffff; border-radius:12px; overflow:hidden;">
        <tr>
            <td style="background:#4338ca; padding:20px 24px; color:#ffffff; font-size:18px; font-weight:bold;">
                École Manager
            </td>
        </tr>
        <tr>
            <td style="padding:24px; color:#1e293b;">
                <p style="margin:0 0 12px;">Bonjour,</p>

                @if($enRetard)
                    <p style="margin:0 0 12px;">
                        Le paiement de <strong>{{ $eleve->nomComplet() }}</strong> ({{ ucfirst($paiement->type) }})
                        est <strong style="color:#dc2626;">en retard</strong> depuis le
                        {{ $paiement->date_echeance->format('d/m/Y') }}.
                    </p>
                @else
                    <p style="margin:0 0 12px;">
                        Le paiement de <strong>{{ $eleve->nomComplet() }}</strong> ({{ ucfirst($paiement->type) }})
                        arrive à échéance le <strong>{{ $paiement->date_echeance->format('d/m/Y') }}</strong>.
                    </p>
                @endif

                <p style="margin:0 0 12px;">Montant restant dû : <strong>{{ number_format($solde, 0, ',', ' ') }} FCFA</strong></p>

                <p style="margin:20px 0 0; font-size:13px; color:#64748b;">
                    Merci de régulariser cette échéance auprès de l'administration de l'école.
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
