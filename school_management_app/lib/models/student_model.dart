import 'package:flutter/foundation.dart';

/**
 * Modèle de données représentant un élève dans le système
 * Contient toutes les informations personnelles, académiques et médicales d'un élève
 */
class Student {
  // ===== INFORMATIONS DE BASE =====
  final String id;                    // Identifiant unique de l'élève
  final String firstName;             // Prénom de l'élève
  final String lastName;              // Nom de famille de l'élève
  final DateTime dateOfBirth;         // Date de naissance
  final String gender;                // Genre (Masculin/Féminin)
  final String address;               // Adresse complète
  final String phoneNumber;           // Numéro de téléphone principal
  final String? email;                // Adresse email (optionnel)
  
  // ===== INFORMATIONS ACADÉMIQUES =====
  final String parentId;              // ID du parent/tuteur légal
  final String classId;               // ID de la classe de l'élève
  final DateTime? admissionDate;      // Date d'admission à l'école
  final bool isActive;                // Statut actif/inactif de l'élève
  
  // ===== INFORMATIONS MÉDICALES =====
  final String? bloodGroup;           // Groupe sanguin
  final String? allergies;            // Allergies connues
  final String? medicalNotes;         // Notes médicales importantes
  
  // ===== INFORMATIONS ADDITIONNELLES =====
  final String? photoUrl;             // URL de la photo de profil
  final Map<String, dynamic>? additionalInfo; // Informations additionnelles flexibles
  
  // ===== MÉTADONNÉES =====
  final DateTime createdAt;           // Date de création de l'enregistrement
  final DateTime updatedAt;           // Date de dernière mise à jour

  Student({
    required this.id,
    required this.firstName,
    required this.lastName,
    required this.dateOfBirth,
    required this.gender,
    required this.address,
    required this.phoneNumber,
    this.email,
    required this.parentId,
    required this.classId,
    this.photoUrl,
    this.admissionDate,
    this.bloodGroup,
    this.allergies,
    this.medicalNotes,
    this.additionalInfo,
    this.isActive = true,              // Par défaut, l'élève est actif
    DateTime? createdAt,
    DateTime? updatedAt,
  })  : createdAt = createdAt ?? DateTime.now(),      // Date de création par défaut si non spécifiée
        updatedAt = updatedAt ?? DateTime.now();       // Date de mise à jour par défaut si non spécifiée

  // ===== GETTERS CALCULÉS =====
  
  // Retourne le nom complet de l'élève
  String get fullName => '$firstName $lastName';
  
  // Calcule l'âge actuel de l'élève en années
  int get age => DateTime.now().difference(dateOfBirth).inDays ~/ 365;

  // ===== SÉRIALISATION JSON =====
  
  // Convertit un objet JSON en instance de Student
  // Gère la conversion des noms de champs snake_case vers camelCase
  factory Student.fromJson(Map<String, dynamic> json) {
    return Student(
      id: json['id'].toString(),
      firstName: json['first_name'],
      lastName: json['last_name'],
      dateOfBirth: DateTime.parse(json['date_of_birth']),
      gender: json['gender'],
      address: json['address'],
      phoneNumber: json['phone_number'],
      email: json['email'],
      parentId: json['parent_id'].toString(),
      classId: json['class_id'].toString(),
      photoUrl: json['photo_url'],
      admissionDate: json['admission_date'] != null 
          ? DateTime.parse(json['admission_date']) 
          : null,
      bloodGroup: json['blood_group'],
      allergies: json['allergies'],
      medicalNotes: json['medical_notes'],
      additionalInfo: json['additional_info'],
      isActive: json['is_active'] ?? true,
      createdAt: json['created_at'] != null 
          ? DateTime.parse(json['created_at']) 
          : null,
      updatedAt: json['updated_at'] != null 
          ? DateTime.parse(json['updated_at']) 
          : null,
    );
  }

  // Convertit une instance de Student en objet JSON
  // Gère la conversion des noms de champs camelCase vers snake_case
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'first_name': firstName,
      'last_name': lastName,
      'date_of_birth': dateOfBirth.toIso8601String(),
      'gender': gender,
      'address': address,
      'phone_number': phoneNumber,
      'email': email,
      'parent_id': parentId,
      'class_id': classId,
      'photo_url': photoUrl,
      'admission_date': admissionDate?.toIso8601String(),
      'blood_group': bloodGroup,
      'allergies': allergies,
      'medical_notes': medicalNotes,
      'additional_info': additionalInfo,
      'is_active': isActive,
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  // ===== MÉTHODE DE MODIFICATION IMMUTABLE =====
  // Crée une copie de l'objet avec certaines propriétés modifiées
  // Respecte le principe d'immutabilité des objets en Flutter
  
  Student copyWith({
    String? id,
    String? firstName,
    String? lastName,
    DateTime? dateOfBirth,
    String? gender,
    String? address,
    String? phoneNumber,
    String? email,
    String? parentId,
    String? classId,
    String? photoUrl,
    DateTime? admissionDate,
    String? bloodGroup,
    String? allergies,
    String? medicalNotes,
    Map<String, dynamic>? additionalInfo,
    bool? isActive,
  }) {
    return Student(
      id: id ?? this.id,
      firstName: firstName ?? this.firstName,
      lastName: lastName ?? this.lastName,
      dateOfBirth: dateOfBirth ?? this.dateOfBirth,
      gender: gender ?? this.gender,
      address: address ?? this.address,
      phoneNumber: phoneNumber ?? this.phoneNumber,
      email: email ?? this.email,
      parentId: parentId ?? this.parentId,
      classId: classId ?? this.classId,
      photoUrl: photoUrl ?? this.photoUrl,
      admissionDate: admissionDate ?? this.admissionDate,
      bloodGroup: bloodGroup ?? this.bloodGroup,
      allergies: allergies ?? this.allergies,
      medicalNotes: medicalNotes ?? this.medicalNotes,
      additionalInfo: additionalInfo ?? this.additionalInfo,
      isActive: isActive ?? this.isActive,
      createdAt: createdAt,                 // Conserve la date de création originale
      updatedAt: DateTime.now(),             // Met à jour la date de modification
    );
  }

  // ===== MÉTHODES DE COMPARAISON ET AFFICHAGE =====
  
  // Représentation textuelle de l'objet pour le débogage
  @override
  String toString() => 'Student(id: $id, name: $fullName, classId: $classId)';

  // Comparaison d'égalité basée sur l'ID
  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is Student && other.id == id;
  }

  // Hash code basé sur l'ID pour les collections
  @override
  int get hashCode => id.hashCode;
}
