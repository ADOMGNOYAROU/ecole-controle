// Importation des packages Flutter nécessaires
import 'package:flutter/material.dart';
// Importation du package Provider pour la gestion d'état
import 'package:provider/provider.dart';
// Importation du provider d'authentification
import 'providers/auth_provider.dart';
// Importation des écrans de l'application
import 'screens/splash_screen.dart';
import 'screens/auth/login_screen.dart';
import 'screens/admin/admin_dashboard.dart';
import 'screens/enseignant/enseignant_dashboard.dart';

// Point d'entrée principal de l'application Flutter
void main() {
  runApp(const MyApp());
}

// Widget principal de l'application qui est un StatelessWidget
class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    // Configuration du Provider pour la gestion d'état globale
    return MultiProvider(
      providers: [
        // Création du provider d'authentification pour gérer l'état de connexion
        ChangeNotifierProvider(create: (_) => AuthProvider()),
      ],
      child: MaterialApp(
        title: 'School Manager', // Titre de l'application
        debugShowCheckedModeBanner: false, // Désactivation de la bannière de debug
        theme: ThemeData(
          colorScheme: ColorScheme.fromSeed(seedColor: Colors.blue), // Schéma de couleurs basé sur le bleu
          useMaterial3: true, // Utilisation du thème Material Design 3
        ),
        initialRoute: '/', // Route initiale au lancement de l'application
        routes: {
          // Définition des routes de navigation de l'application
          '/': (context) => const SplashScreen(), // Page d'accueil / écran de chargement
          '/login': (context) => const LoginScreen(), // Page de connexion
          '/admin-dashboard': (context) => const AdminDashboard(), // Tableau de bord administrateur
          '/enseignant-dashboard': (context) => const EnseignantDashboard(), // Tableau de bord enseignant
        },
      ),
    );
  }
}
