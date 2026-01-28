import 'package:flutter/material.dart';

/**
 * Classe contenant toutes les couleurs de l'application
 * Centralise la charte graphique pour une cohérence visuelle
 */
class AppColors {
  // ===== COULEURS PRINCIPALES =====
  static const Color primary = Color(0xFF2196F3);      // Bleu principal - utilisé pour les éléments interactifs principaux
  static const Color secondary = Color(0xFFFF5722);    // Orange secondaire - utilisé pour les accents et actions secondaires
  
  // ===== COULEURS DE STATUT =====
  static const Color success = Color(0xFF4CAF50);      // Vert - pour les succès, validations et confirmations
  static const Color error = Color(0xFFF44336);        // Rouge - pour les erreurs et alertes importantes
  static const Color warning = Color(0xFFFFC107);      // Jaune - pour les avertissements et notifications
  static const Color info = Color(0xFF2196F3);         // Bleu - pour les informations et notifications
  
  // ===== COULEURS NEUTRES =====
  static const Color white = Color(0xFFFFFFFF);        // Blanc pur - fonds, textes sur fond sombre
  static const Color black = Color(0xFF000000);        // Noir pur - textes principaux sur fond clair
  static const Color grey = Color(0xFF9E9E9E);          // Gris moyen - éléments désactivés, textes secondaires
  static const Color lightGrey = Color(0xFFE0E0E0);    // Gris clair - bordures, séparateurs, fonds
  static const Color darkGrey = Color(0xFF424242);      // Gris foncé - textes moins importants
  
  // ===== COULEURS DE FOND =====
  static const Color background = Color(0xFFFAFAFA);   // Fond principal de l'application (gris très clair)
  static const Color cardBackground = Color(0xFFFFFFFF); // Fond des cartes et conteneurs
  
  // ===== COULEURS DE TEXTE =====
  static const Color textPrimary = Color(0xFF212121);   // Texte principal - titres, contenus importants
  static const Color textSecondary = Color(0xFF757575); // Texte secondaire - sous-titres, descriptions
  static const Color textHint = Color(0xFFBDBDBD);      // Texte d'aide - placeholders, hints
}
