import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/auth_provider.dart';
import '../core/constants/app_colors.dart';

/**
 * Écran de démarrage (Splash Screen)
 * Premier écran affiché lors du lancement de l'application
 * Vérifie l'état de connexion et redirige vers l'écran approprié
 */
class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    _checkAuth(); // Lance la vérification d'authentification au démarrage
  }

  // ===== VÉRIFICATION D'AUTHENTIFICATION =====
  // Vérifie si un utilisateur est déjà connecté et redirige vers le bon écran
  
  Future<void> _checkAuth() async {
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    
    // Attendre 2 secondes pour l'effet visuel du splash
    await Future.delayed(const Duration(seconds: 2));
    
    // Vérifier si l'utilisateur a une session valide
    final isLoggedIn = await authProvider.checkAuth();
    
    // Vérifier que le widget est toujours monté avant la navigation
    if (!mounted) return;
    
    if (isLoggedIn) {
      // Utilisateur connecté → Redirection selon le rôle
      final user = authProvider.user!;
      
      if (user.isAdmin) {
        // Administrateur → Tableau de bord admin
        Navigator.pushReplacementNamed(context, '/admin-dashboard');
      } else if (user.isEnseignant) {
        // Enseignant → Tableau de bord enseignant
        Navigator.pushReplacementNamed(context, '/enseignant-dashboard');
      } else {
        // Autre rôle → Redirection vers login par défaut
        Navigator.pushReplacementNamed(context, '/login');
      }
    } else {
      // Utilisateur non connecté → Écran de connexion
      Navigator.pushReplacementNamed(context, '/login');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.primary, // Fond bleu principal
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            // ===== LOGO DE L'APPLICATION =====
            Icon(
              Icons.school,           // Icône d'école
              size: 100,              // Taille du logo
              color: AppColors.white, // Couleur blanche
            ),
            const SizedBox(height: 20),
            
            // ===== NOM DE L'APPLICATION =====
            Text(
              'School Manager',
              style: TextStyle(
                fontSize: 32,
                fontWeight: FontWeight.bold,
                color: AppColors.white,
              ),
            ),
            const SizedBox(height: 10),
            
            // ===== SOUS-TITRE =====
            Text(
              'Gestion d\'école simplifiée',
              style: TextStyle(
                fontSize: 16,
                color: AppColors.white.withOpacity(0.8), // Légèrement transparent
              ),
            ),
            const SizedBox(height: 40),
            
            // ===== INDICATEUR DE CHARGEMENT =====
            // Montre que l'application travaille en arrière-plan
            CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(AppColors.white),
            ),
          ],
        ),
      ),
    );
  }
}
