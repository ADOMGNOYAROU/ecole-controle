/**
 * Modèle de données représentant un utilisateur du système
 * Contient les informations de base communes à tous les types d'utilisateurs
 */
class User {
  final int id;              // Identifiant unique de l'utilisateur
  final String name;         // Nom complet de l'utilisateur
  final String email;        // Adresse email de l'utilisateur
  final String role;         // Rôle dans le système (admin, enseignant, eleve, parent)
  final String? telephone;   // Numéro de téléphone (optionnel)

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.telephone,
  });

  // ===== SÉRIALISATION JSON =====
  
  // Convertit un objet JSON en instance de User
  // Utilisé pour désérialiser les réponses de l'API
  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      role: json['role'],
      telephone: json['telephone'],
    );
  }

  // Convertit une instance de User en objet JSON
  // Utilisé pour sérialiser les données avant envoi à l'API
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'telephone': telephone,
    };
  }

  // ===== MÉTHODES UTILITAIRES =====
  
  // Vérifie si l'utilisateur est un administrateur
  bool get isAdmin => role == 'admin';
  
  // Vérifie si l'utilisateur est un enseignant
  bool get isEnseignant => role == 'enseignant';
  
  // Vérifie si l'utilisateur est un élève
  bool get isEleve => role == 'eleve';
  
  // Vérifie si l'utilisateur est un parent
  bool get isParent => role == 'parent';
}
