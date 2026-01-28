<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vos identifiants de connexion</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3b82f6;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #3b82f6;
            margin-bottom: 10px;
        }
        .title {
            font-size: 28px;
            color: #1f2937;
            margin: 20px 0;
        }
        .welcome {
            font-size: 18px;
            color: #6b7280;
            margin-bottom: 25px;
        }
        .credentials {
            background-color: #f0f9ff;
            border: 2px solid #3b82f6;
            border-radius: 8px;
            padding: 20px;
            margin: 25px 0;
        }
        .credential-item {
            margin: 15px 0;
            display: flex;
            align-items: center;
        }
        .label {
            font-weight: bold;
            color: #374151;
            min-width: 80px;
        }
        .value {
            color: #1f2937;
            font-family: 'Courier New', monospace;
            background-color: #e5e7eb;
            padding: 5px 10px;
            border-radius: 4px;
            flex: 1;
        }
        .button {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #2563eb;
        }
        .info-box {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .additional-info {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏫 {{ config('app.name') }}</div>
            <h1 class="title">Vos identifiants de connexion</h1>
        </div>

        <p class="welcome">
            Bonjour <strong>{{ $accountInfo['name'] }}</strong>,
        </p>

        <p>
            Nous sommes ravis de vous accueillir dans notre système de gestion scolaire. 
            Vos identifiants de connexion ont été créés avec succès.
        </p>

        @if($accountInfo['type'] === 'Élève')
            <p>
                En tant qu'élève, vous pourrez consulter vos notes, vos absences, 
                et suivre votre progression scolaire.
            </p>
        @else
            <p>
                En tant que parent, vous pourrez suivre la scolarité de vos enfants, 
                consulter leurs notes, leurs absences et communiquer avec l'équipe pédagogique.
            </p>
        @endif

        <div class="credentials">
            <h3 style="margin-top: 0; color: #1f2937;">🔐 Vos identifiants</h3>
            
            <div class="credential-item">
                <span class="label">Email :</span>
                <span class="value">{{ $accountInfo['email'] }}</span>
            </div>
            
            <div class="credential-item">
                <span class="label">Mot de passe :</span>
                <span class="value">{{ $accountInfo['password'] }}</span>
            </div>
        </div>

        @if(isset($accountInfo['classe']))
            <div class="additional-info">
                <h4 style="margin-top: 0; color: #374151;">📚 Informations sur votre classe</h4>
                <p><strong>Classe :</strong> {{ $accountInfo['classe'] }}</p>
            </div>
        @endif

        @if(isset($accountInfo['enfants']))
            <div class="additional-info">
                <h4 style="margin-top: 0; color: #374151;">👨‍👩‍👧‍👦 Vos enfants</h4>
                <p><strong>Élèves concernés :</strong> {{ $accountInfo['enfants'] }}</p>
            </div>
        @endif

        <div style="text-align: center;">
            <a href="{{ url('/login') }}" class="button">
                🚀 Me connecter maintenant
            </a>
        </div>

        <div class="info-box">
            <strong>⚠️ Important :</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Conservez ces identifiants dans un endroit sécurisé</li>
                <li>Vous pourrez changer votre mot de passe après votre première connexion</li>
                <li>Cet email contient vos informations confidentielles, ne le partagez pas</li>
                <li>En cas de problème, contactez l'administration de l'école</li>
            </ul>
        </div>

        <p>
            Pour toute question ou assistance, n'hésitez pas à contacter notre équipe technique :
        </p>
        <p>
            📧 Email : support@ecole.school<br>
            📞 Téléphone : 01 23 45 67 89
        </p>

        <div class="footer">
            <p>
                Cordialement,<br>
                L'équipe administrative de {{ config('app.name') }}<br>
                <small>Cet email a été généré automatiquement le {{ now()->format('d/m/Y à H:i') }}</small>
            </p>
        </div>
    </div>
</body>
</html>
