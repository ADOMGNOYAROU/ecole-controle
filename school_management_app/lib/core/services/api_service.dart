import 'dart:convert';
import 'package:http/http.dart' as http;
import '../constants/api_constants.dart';
import 'storage_service.dart';

/**
 * Service de gestion des appels API
 * Utilise le pattern Singleton pour garantir une seule instance
 * Gère toutes les communications avec le backend Laravel
 */
class ApiService {
  // Instance unique (Singleton) - assure qu'une seule instance existe dans toute l'application
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;
  ApiService._internal();

  final _storage = StorageService(); // Service de stockage pour gérer les tokens

  // ===== EN-TÊTES HTTP =====
  
  // Headers par défaut pour les requêtes sans authentification
  Map<String, String> get _headers => {
    'Content-Type': 'application/json',      // Format des données envoyées
    'Accept': 'application/json',           // Format des données attendues
    'Access-Control-Allow-Origin': '*',     // Autorise les requêtes de toutes les origines (CORS)
    'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS', // Méthodes HTTP autorisées
    'Access-Control-Allow-Headers': 'Origin, Content-Type, X-Auth-Token, Authorization', // En-têtes autorisés
  };

  // Headers avec token d'authentification pour les requêtes protégées
  Future<Map<String, String>> get _authHeaders async {
    final token = await _storage.getToken(); // Récupération du token depuis le stockage sécurisé
    return {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'Access-Control-Allow-Origin': '*',
      'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
      'Access-Control-Allow-Headers': 'Origin, Content-Type, X-Auth-Token, Authorization',
      'Authorization': 'Bearer $token', // Token Bearer pour l'authentification
    };
  }

  // ===== MÉTHODE HTTP GET =====
  // Utilisée pour récupérer des données depuis le serveur
  
  Future<Map<String, dynamic>> get(String endpoint, {bool requiresAuth = false}) async {
    try {
      final headers = requiresAuth ? await _authHeaders : _headers; // Choix des headers selon authentification
      
      final response = await http.get(
        Uri.parse(endpoint),
        headers: headers,
      ).timeout(const Duration(seconds: 30)); // Timeout de 30 secondes pour éviter l'attente infinie

      return _handleResponse(response); // Traitement de la réponse
    } on http.ClientException catch (e) {
      // Erreur de connexion réseau
      throw Exception('Erreur de connexion au serveur: ${e.message}');
    } on Exception catch (e) {
      // Autre erreur inattendue
      throw Exception('Erreur inattendue: $e');
    }
  }

  // ===== MÉTHODE HTTP POST =====
  // Utilisée pour créer de nouvelles ressources sur le serveur
  
  Future<Map<String, dynamic>> post(
    String endpoint,
    Map<String, dynamic> data, {
    bool requiresAuth = false,
  }) async {
    try {
      final headers = requiresAuth ? await _authHeaders : _headers; // Choix des headers selon authentification
      
      final response = await http.post(
        Uri.parse(endpoint),
        headers: headers,
        body: jsonEncode(data), // Conversion des données en JSON
      );

      return _handleResponse(response); // Traitement de la réponse
    } catch (e) {
      // Erreur générique de connexion
      throw Exception('Erreur de connexion : $e');
    }
  }

  // ===== MÉTHODE HTTP PUT =====
  // Utilisée pour mettre à jour des ressources existantes sur le serveur
  
  Future<Map<String, dynamic>> put(
    String endpoint,
    Map<String, dynamic> data, {
    bool requiresAuth = true, // Par défaut, les mises à jour nécessitent une authentification
  }) async {
    try {
      final headers = requiresAuth ? await _authHeaders : _headers; // Choix des headers selon authentification
      
      final response = await http.put(
        Uri.parse(endpoint),
        headers: headers,
        body: jsonEncode(data), // Conversion des données en JSON
      );

      return _handleResponse(response); // Traitement de la réponse
    } catch (e) {
      // Erreur générique de connexion
      throw Exception('Erreur de connexion : $e');
    }
  }

  // ===== MÉTHODE HTTP DELETE =====
  // Utilisée pour supprimer des ressources sur le serveur
  
  Future<Map<String, dynamic>> delete(
    String endpoint, {
    bool requiresAuth = true, // Par défaut, les suppressions nécessitent une authentification
  }) async {
    try {
      final headers = requiresAuth ? await _authHeaders : _headers; // Choix des headers selon authentification
      
      final response = await http.delete(
        Uri.parse(endpoint),
        headers: headers,
      );

      return _handleResponse(response); // Traitement de la réponse
    } catch (e) {
      // Erreur générique de connexion
      throw Exception('Erreur de connexion : $e');
    }
  }

  // ===== GESTION DES RÉPONSES HTTP =====
  // Méthode centrale pour traiter toutes les réponses du serveur
  
  Map<String, dynamic> _handleResponse(http.Response response) {
    final data = jsonDecode(response.body); // Décodage de la réponse JSON

    if (response.statusCode >= 200 && response.statusCode < 300) {
      // Succès (codes 200-299) : la requête a été traitée avec succès
      return data;
    } else if (response.statusCode == 401) {
      // Non authentifié (401) : token invalide ou expiré
      throw Exception('Session expirée. Veuillez vous reconnecter.');
    } else if (response.statusCode == 422) {
      // Erreur de validation (422) : données envoyées invalides
      final errors = data['errors'] as Map<String, dynamic>;
      final firstError = errors.values.first[0]; // Extraction de la première erreur de validation
      throw Exception(firstError);
    } else {
      // Autre erreur : utilisation du message d'erreur du serveur ou message par défaut
      throw Exception(data['message'] ?? 'Une erreur est survenue');
    }
  }
}
