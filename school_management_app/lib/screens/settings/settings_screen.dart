import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class SettingsScreen extends StatelessWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Paramètres',
          style: GoogleFonts.poppins(
            color: Colors.white,
            fontWeight: FontWeight.w600,
          ),
        ),
        backgroundColor: Colors.blueGrey,
      ),
      body: ListView(
        children: [
          const SizedBox(height: 16),
          ListTile(
            leading: const Icon(Icons.person, color: Colors.blueGrey),
            title: const Text('Profil'),
            subtitle: const Text('Gérer votre profil utilisateur'),
            onTap: () {
              // Navigation vers l'édition du profil
            },
          ),
          const Divider(),
          ListTile(
            leading: const Icon(Icons.notifications, color: Colors.blueGrey),
            title: const Text('Notifications'),
            subtitle: const Text('Gérer les préférences de notification'),
            onTap: () {
              // Navigation vers les paramètres de notification
            },
          ),
          const Divider(),
          ListTile(
            leading: const Icon(Icons.lock, color: Colors.blueGrey),
            title: const Text('Sécurité'),
            subtitle: const Text('Changer le mot de passe'),
            onTap: () {
              // Navigation vers les paramètres de sécurité
            },
          ),
          const Divider(),
          ListTile(
            leading: const Icon(Icons.info, color: Colors.blueGrey),
            title: const Text('À propos'),
            subtitle: const Text('Version 1.0.0'),
            onTap: () {
              // Afficher la boîte de dialogue À propos
              showAboutDialog(
                context: context,
                applicationName: 'Gestion Scolaire',
                applicationVersion: '1.0.0',
                applicationIcon: const Icon(Icons.school, size: 50, color: Colors.blueGrey),
                children: const [
                  Text('Application de gestion scolaire'),
                  SizedBox(height: 8),
                  Text('© 2024 Tous droits réservés'),
                ],
              );
            },
          ),
        ],
      ),
    );
  }
}
