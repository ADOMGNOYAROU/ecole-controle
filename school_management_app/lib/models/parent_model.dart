/**
 * Modèle de données représentant un parent/tuteur dans le système
 * Contient les informations personnelles et de contact d'un parent d'élève
 */
class Parent {
  // ===== INFORMATIONS PERSONNELLES =====
  final String id;              // Identifiant unique du parent
  final String nom;             // Nom de famille du parent
  final String prenom;          // Prénom du parent
  final String email;           // Adresse email du parent
  final String telephone;       // Numéro de téléphone principal
  final String adresse;         // Adresse complète du parent
  
  // ===== INFORMATIONS ADDITIONNELLES =====
  final String? profession;     // Profession du parent (optionnel)
  final String? photoUrl;       // URL de la photo de profil (optionnel)

  Parent({
    required this.id,
    required this.nom,
    required this.prenom,
    required this.email,
    required this.telephone,
    required this.adresse,
    this.profession,
    this.photoUrl,
  });

  // ===== SÉRIALISATION JSON =====
  
  // Convertit un objet JSON en instance de Parent
  // Utilisé pour désérialiser les réponses de l'API
  factory Parent.fromJson(Map<String, dynamic> json) {
    return Parent(
      id: json['id'].toString(),
      nom: json['nom'],
      prenom: json['prenom'],
      email: json['email'],
      telephone: json['telephone'],
      adresse: json['adresse'],
      profession: json['profession'],
      photoUrl: json['photo'], // Note: le champ JSON est 'photo' et non 'photoUrl'
    );
  }

  // Convertit une instance de Parent en objet JSON
  // Utilisé pour sérialiser les données avant envoi à l'API
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nom': nom,
      'prenom': prenom,
      'email': email,
      'telephone': telephone,
      'adresse': adresse,
      'profession': profession,
      'photo': photoUrl, // Note: le champ JSON est 'photo' et non 'photoUrl'
    };
  }

  // ===== MÉTHODE UTILITAIRE =====
  
  // Retourne le nom complet du parent (prénom + nom)
  String get fullName => '$prenom $nom';
}
