/**
 * Classe contenant toutes les constantes de l'API
 * Centralise les URLs et endpoints pour faciliter la maintenance
 */
class ApiConstants {
  // URL de base de ton API Laravel
  // ⚠️ IMPORTANT : Change cette URL selon ton environnement
  
  // Pour le web :
  // static const String baseUrl = 'http://localhost:8000/api';
  
  // Pour l'émulateur Android :
  // static const String baseUrl = 'http://10.0.2.2:8000/api';
  
  // Pour le développement local :
  static const String baseUrl = 'http://192.168.1.67:8000/api';
  
  // ===== ENDPOINTS D'AUTHENTIFICATION =====
  static const String login = '$baseUrl/auth/login';      // Connexion utilisateur
  static const String register = '$baseUrl/auth/register'; // Inscription utilisateur
  static const String logout = '$baseUrl/auth/logout';    // Déconnexion utilisateur
  static const String user = '$baseUrl/auth/me';          // Informations utilisateur connecté
  
  // ===== ENDPOINTS DE GESTION DES CLASSES =====
  static const String classes = '$baseUrl/classes';        // CRUD des classes
  
  // ===== ENDPOINTS DE GESTION DES ÉLÈVES =====
  static const String eleves = '$baseUrl/eleves';          // CRUD des élèves
  
  // ===== ENDPOINTS DE GESTION DES ENSEIGNANTS =====
  static const String enseignants = '$baseUrl/enseignants'; // CRUD des enseignants
  
  // ===== ENDPOINTS DE GESTION DES MATIÈRES =====
  static const String matieres = '$baseUrl/matieres';      // CRUD des matières
  
  // ===== ENDPOINTS DE GESTION DES PRÉSENCES =====
  static const String presences = '$baseUrl/presences';    // Gestion des présences/absences
  
  // ===== ENDPOINTS DE GESTION DES NOTES =====
  static const String notes = '$baseUrl/notes';            // Gestion des notes des élèves
}
