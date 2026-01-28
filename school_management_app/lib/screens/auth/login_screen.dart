import 'dart:async';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';

/**
 * Écran de connexion (Login Screen)
 * Permet aux utilisateurs de s'authentifier avec email et mot de passe
 * Inclut des options de connexion sociale (non fonctionnelles pour l'instant)
 */
class LoginScreen extends StatefulWidget {
  const LoginScreen({Key? key}) : super(key: key);

  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  // ===== CONTRÔLEURS ET ÉTAT =====
  final _formKey = GlobalKey<FormState>();           // Clé du formulaire pour la validation
  final _emailController = TextEditingController();  // Contrôleur pour le champ email
  final _passwordController = TextEditingController(); // Contrôleur pour le champ mot de passe
  bool _isLoading = false;                            // État de chargement pendant la connexion
  bool _obscurePassword = true;                       // Contrôle la visibilité du mot de passe

  // ===== WIDGET POUR CARTES SOCIALES =====
  // Crée une carte stylisée pour les options de connexion sociale
  
  Widget _socialCard({
    required IconData icon,      // Icône à afficher
    required String label,       // Texte du bouton
    required Color accent,       // Couleur d'accent
    required VoidCallback onTap,  // Action au clic
  }) {
    final theme = Theme.of(context);

    return Material(
      color: theme.colorScheme.surface,
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        borderRadius: BorderRadius.circular(14),
        onTap: onTap,
        child: Container(
          width: 190,
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: theme.colorScheme.outlineVariant,
            ),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Conteneur pour l'icône avec fond coloré
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: accent.withOpacity(0.12), // Fond semi-transparent
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, color: accent),
              ),
              const SizedBox(width: 12),
              // Texte du label
              Expanded(
                child: Text(
                  label,
                  style: theme.textTheme.titleSmall?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ===== NETTOYAGE DES RESSOURCES =====
  // Libère les contrôleurs lorsque le widget est détruit
  
  @override
  void dispose() {
    _emailController.dispose();   // Nettoie le contrôleur d'email
    _passwordController.dispose(); // Nettoie le contrôleur de mot de passe
    super.dispose();              // Appelle la méthode dispose parente
  }

  // ===== MÉTHODE DE CONNEXION =====
  // Gère le processus de connexion avec validation et gestion des erreurs
  
  Future<void> _login() async {
    // Validation du formulaire avant la connexion
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isLoading = true); // Active l'indicateur de chargement
    
    try {
      // Appel au provider d'authentification
      final success = await Provider.of<AuthProvider>(context, listen: false).login(
        _emailController.text.trim(),    // Email nettoyé
        _passwordController.text,         // Mot de passe
      );
      
      if (success && mounted) {
        // Connexion réussie → Redirection selon le rôle
        final user = Provider.of<AuthProvider>(context, listen: false).user!;
        
        if (user.isAdmin) {
          Navigator.pushReplacementNamed(context, '/admin-dashboard');
        } else if (user.isEnseignant) {
          Navigator.pushReplacementNamed(context, '/enseignant-dashboard');
        } else {
          // Par défaut, redirige vers le dashboard admin
          Navigator.pushReplacementNamed(context, '/admin-dashboard');
        }
      } else if (mounted) {
        // Échec de connexion → Affichage d'un message d'erreur
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Échec de la connexion. Veuillez vérifier vos identifiants.'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } on http.ClientException catch (e) {
      // Erreur de connexion réseau
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur de connexion: ${e.message}'),
            backgroundColor: Colors.orange,
          ),
        );
      }
    } on FormatException catch (e) {
      // Erreur de format de données
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur de format de données: ${e.message}'),
            backgroundColor: Colors.orange,
          ),
        );
      }
    } on TimeoutException {
      // Délai d'attente dépassé
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('La connexion a expiré. Veuillez réessayer.'),
            backgroundColor: Colors.orange,
          ),
        );
      }
    } catch (e) {
      // Erreur inattendue
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur inattendue: ${e.toString()}'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      // Désactive l'indicateur de chargement dans tous les cas
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      body: SafeArea(
        child: Container(
          width: double.infinity,
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [
                theme.colorScheme.primary.withOpacity(0.18),
                theme.colorScheme.secondary.withOpacity(0.12),
                theme.colorScheme.surface,
              ],
            ),
          ),
          child: LayoutBuilder(
            builder: (context, constraints) {
              return Center(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 440),
                    child: Card(
                      elevation: 2,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(24),
                        child: Form(
                          key: _formKey,
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              Center(
                                child: Container(
                                  width: 72,
                                  height: 72,
                                  decoration: BoxDecoration(
                                    color: theme.colorScheme.primary.withOpacity(0.12),
                                    shape: BoxShape.circle,
                                  ),
                                  child: Icon(
                                    Icons.school,
                                    size: 36,
                                    color: theme.colorScheme.primary,
                                  ),
                                ),
                              ),
                              const SizedBox(height: 16),
                              Text(
                                'Connexion',
                                textAlign: TextAlign.center,
                                style: theme.textTheme.headlineSmall?.copyWith(
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                              const SizedBox(height: 6),
                              Text(
                                'Accédez à votre espace de gestion',
                                textAlign: TextAlign.center,
                                style: theme.textTheme.bodyMedium?.copyWith(
                                  color: theme.colorScheme.onSurfaceVariant,
                                ),
                              ),
                              const SizedBox(height: 24),
                              TextFormField(
                                controller: _emailController,
                                decoration: InputDecoration(
                                  labelText: 'Email',
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(14),
                                  ),
                                  filled: true,
                                  fillColor: theme.colorScheme.surfaceContainerHighest.withOpacity(0.5),
                                  prefixIcon: const Icon(Icons.email),
                                ),
                                keyboardType: TextInputType.emailAddress,
                                validator: (value) {
                                  if (value == null || value.isEmpty) {
                                    return 'Veuillez entrer votre email';
                                  }
                                  if (!value.contains('@')) {
                                    return 'Veuillez entrer un email valide';
                                  }
                                  return null;
                                },
                              ),
                              const SizedBox(height: 14),
                              TextFormField(
                                controller: _passwordController,
                                decoration: InputDecoration(
                                  labelText: 'Mot de passe',
                                  border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(14),
                                  ),
                                  filled: true,
                                  fillColor: theme.colorScheme.surfaceContainerHighest.withOpacity(0.5),
                                  prefixIcon: const Icon(Icons.lock),
                                  suffixIcon: IconButton(
                                    onPressed: () {
                                      setState(() => _obscurePassword = !_obscurePassword);
                                    },
                                    icon: Icon(
                                      _obscurePassword ? Icons.visibility : Icons.visibility_off,
                                    ),
                                  ),
                                ),
                                obscureText: _obscurePassword,
                                validator: (value) {
                                  if (value == null || value.isEmpty) {
                                    return 'Veuillez entrer votre mot de passe';
                                  }
                                  if (value.length < 6) {
                                    return 'Le mot de passe doit contenir au moins 6 caractères';
                                  }
                                  return null;
                                },
                              ),
                              const SizedBox(height: 20),
                              SizedBox(
                                height: 50,
                                child: ElevatedButton(
                                  style: ElevatedButton.styleFrom(
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(14),
                                    ),
                                  ),
                                  onPressed: _isLoading ? null : _login,
                                  child: _isLoading
                                      ? Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            SizedBox(
                                              width: 22,
                                              height: 22,
                                              child: CircularProgressIndicator(
                                                strokeWidth: 2.5,
                                                valueColor: AlwaysStoppedAnimation<Color>(
                                                  theme.colorScheme.onPrimary,
                                                ),
                                              ),
                                            ),
                                            const SizedBox(width: 12),
                                            const Text('Connexion...'),
                                          ],
                                        )
                                      : Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            const Icon(Icons.login, size: 20),
                                            const SizedBox(width: 10),
                                            const Text('Se connecter'),
                                          ],
                                        ),
                                ),
                              ),
                              const SizedBox(height: 18),
                              Row(
                                children: [
                                  Expanded(
                                    child: Divider(
                                      color: theme.colorScheme.outlineVariant,
                                      height: 1,
                                    ),
                                  ),
                                  const SizedBox(width: 10),
                                  Text(
                                    'ou continuer avec',
                                    style: theme.textTheme.bodySmall?.copyWith(
                                      color: theme.colorScheme.onSurfaceVariant,
                                    ),
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Divider(
                                      color: theme.colorScheme.outlineVariant,
                                      height: 1,
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 16),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Expanded(
                                    child: _socialCard(
                                      icon: Icons.facebook,
                                      label: 'Facebook',
                                      accent: const Color(0xFF1877F2),
                                      onTap: () {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          const SnackBar(content: Text('Connexion Facebook : bientôt disponible')),
                                        );
                                      },
                                    ),
                                  ),
                                  const SizedBox(width: 12),
                                  Expanded(
                                    child: _socialCard(
                                      icon: Icons.g_mobiledata,
                                      label: 'Google',
                                      accent: const Color(0xFFDB4437),
                                      onTap: () {
                                        ScaffoldMessenger.of(context).showSnackBar(
                                          const SnackBar(content: Text('Connexion Google : bientôt disponible')),
                                        );
                                      },
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ),
    );
  }
}
