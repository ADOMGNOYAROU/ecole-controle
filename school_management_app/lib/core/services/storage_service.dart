import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/**
 * Service de stockage sécurisé des données utilisateur
 * Utilise le pattern Singleton pour garantir une seule instance
 * Gère la persistance des tokens et informations utilisateur
 */
class StorageService {
  // Instance unique (Singleton) - assure qu'une seule instance existe dans toute l'application
  static final StorageService _instance = StorageService._internal();
  factory StorageService() => _instance;
  StorageService._internal();

  // Stockage sécurisé utilisant FlutterSecureStorage
  // Les données sont chiffrées et stockées de manière sécurisée sur l'appareil
  final _storage = const FlutterSecureStorage();

  // ===== CLÉS DE STOCKAGE =====
  // Constantes pour éviter les erreurs de frappe et centraliser les noms de clés
  
  static const String _keyToken = 'auth_token';       // Token d'authentification JWT
  static const String _keyUserId = 'user_id';          // ID de l'utilisateur
  static const String _keyUserRole = 'user_role';       // Rôle de l'utilisateur (admin, enseignant, etc.)
  static const String _keyUserName = 'user_name';      // Nom complet de l'utilisateur
  static const String _keyUserEmail = 'user_email';    // Email de l'utilisateur

  // ===== GESTION DU TOKEN D'AUTHENTIFICATION =====
  
  // Sauvegarde du token JWT dans le stockage sécurisé
  Future<void> saveToken(String token) async {
    await _storage.write(key: _keyToken, value: token);
  }

  // Récupération du token JWT depuis le stockage sécurisé
  // Retourne null si aucun token n'est trouvé
  Future<String?> getToken() async {
    return await _storage.read(key: _keyToken);
  }

  // Suppression du token JWT (utilisé lors de la déconnexion)
  Future<void> deleteToken() async {
    await _storage.delete(key: _keyToken);
  }

  // ===== GESTION DES INFORMATIONS UTILISATEUR =====
  
  // Sauvegarde des informations complètes de l'utilisateur
  // Utilisé après une connexion réussie pour stocker les données utilisateur
  Future<void> saveUserInfo({
    required int userId,      // ID unique de l'utilisateur
    required String role,      // Rôle dans le système
    required String name,      // Nom complet
    required String email,    // Adresse email
  }) async {
    await _storage.write(key: _keyUserId, value: userId.toString());
    await _storage.write(key: _keyUserRole, value: role);
    await _storage.write(key: _keyUserName, value: name);
    await _storage.write(key: _keyUserEmail, value: email);
  }

  // Récupération de toutes les informations utilisateur
  // Retourne un Map avec toutes les données ou null si non trouvées
  Future<Map<String, String?>> getUserInfo() async {
    return {
      'userId': await _storage.read(key: _keyUserId),
      'role': await _storage.read(key: _keyUserRole),
      'name': await _storage.read(key: _keyUserName),
      'email': await _storage.read(key: _keyUserEmail),
    };
  }

  // Suppression de toutes les informations utilisateur
  // Utilisé lors de la déconnexion complète
  Future<void> deleteUserInfo() async {
    await _storage.delete(key: _keyUserId);
    await _storage.delete(key: _keyUserRole);
    await _storage.delete(key: _keyUserName);
    await _storage.delete(key: _keyUserEmail);
  }

  // ===== NETTOYAGE COMPLET =====
  
  // Suppression de toutes les données stockées
  // Utilisé pour une déconnexion complète ou une réinitialisation
  Future<void> clearAll() async {
    await _storage.deleteAll();
  }

  // ===== VÉRIFICATION D'AUTHENTIFICATION =====
  
  // Vérifie si un utilisateur est actuellement connecté
  // Retourne true si un token valide existe, false sinon
  Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null && token.isNotEmpty; // Vérifie que le token existe et n'est pas vide
  }
}
