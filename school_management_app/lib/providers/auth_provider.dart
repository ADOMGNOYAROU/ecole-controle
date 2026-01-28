import 'package:flutter/foundation.dart';
import '../models/user_model.dart';
import '../core/services/api_service.dart';
import '../core/services/storage_service.dart';
import '../core/constants/api_constants.dart';

/**
 * Provider d'authentification gérant l'état de connexion des utilisateurs
 * Utilise le pattern ChangeNotifier pour notifier les widgets des changements d'état
 * Gère la connexion, déconnexion, inscription et vérification de session
 */
class AuthProvider with ChangeNotifier {
  // ===== SERVICES =====
  final _apiService = ApiService();   // Service pour les appels API
  final _storage = StorageService(); // Service pour le stockage local sécurisé

  // ===== ÉTAT DU PROVIDER =====
  User? _user;           // Utilisateur actuellement connecté (null si non connecté)
  bool _isLoading = false; // Indicateur de chargement pour les opérations asynchrones
  String? _errorMessage;   // Message d'erreur à afficher à l'utilisateur

  // ===== GETTERS PUBLIQUES =====
  User? get user => _user;           // Accès à l'utilisateur connecté
  bool get isLoading => _isLoading; // État de chargement
  String? get errorMessage => _errorMessage; // Message d'erreur courant
  bool get isLoggedIn => _user != null; // Vérifie si un utilisateur est connecté

  // ===== MÉTHODE DE CONNEXION =====
  // Authentifie un utilisateur avec email et mot de passe
  // Retourne true en cas de succès, false en cas d'échec
  
  Future<bool> login(String email, String password) async {
    try {
      _setLoading(true);  // Active l'indicateur de chargement
      _clearError();      // Efface les erreurs précédentes

      // Appel à l'API de connexion
      final response = await _apiService.post(
        ApiConstants.login,
        {
          'email': email,
          'password': password,
        },
      );

      // Vérification de la réponse de l'API
      if (response['success'] == true) {
        final data = response['data'];
        
        // Sauvegarde du token JWT dans le stockage sécurisé
        await _storage.saveToken(data['access_token']);
        
        // Sauvegarde des informations utilisateur dans le stockage local
        final userData = data['user'];
        await _storage.saveUserInfo(
          userId: userData['id'],
          role: userData['role'],
          name: userData['name'],
          email: userData['email'],
        );
        
        // Création de l'objet User à partir des données
        _user = User.fromJson(userData);
        
        _setLoading(false); // Désactive l'indicateur de chargement
        notifyListeners(); // Notifie les widgets du changement d'état
        
        return true; // Connexion réussie
      } else {
        throw Exception('Échec de la connexion');
      }
    } catch (e) {
      _setError(e.toString()); // Affiche l'erreur à l'utilisateur
      _setLoading(false);      // Désactive l'indicateur de chargement
      return false;            // Connexion échouée
    }
  }

  // ===== MÉTHODE DE DÉCONNEXION =====
  // Déconnecte l'utilisateur et nettoie les données locales
  
  Future<void> logout() async {
    try {
      _setLoading(true); // Active l'indicateur de chargement
      
      // Appel à l'API pour invalider le token côté serveur
      await _apiService.post(
        ApiConstants.logout,
        {},
        requiresAuth: true,
      );
    } catch (e) {
      // Même si l'API échoue, on continue la déconnexion locale
      debugPrint('Erreur lors de la déconnexion : $e');
    } finally {
      // Nettoyage des données locales (exécuté même en cas d'erreur API)
      await _storage.clearAll(); // Supprime token et infos utilisateur
      _user = null;               // Réinitialise l'utilisateur
      _setLoading(false);         // Désactive l'indicateur de chargement
      notifyListeners();         // Notifie les widgets du changement
    }
  }

  // ===== VÉRIFICATION DE SESSION =====
  // Vérifie si l'utilisateur a une session valide au démarrage de l'app
  // Retourne true si la session est valide, false sinon
  
  Future<bool> checkAuth() async {
    try {
      _setLoading(true); // Active l'indicateur de chargement
      
      // Vérifie si un token existe localement
      final isLoggedIn = await _storage.isLoggedIn();
      
      if (!isLoggedIn) {
        _setLoading(false);
        return false; // Pas de token = pas de session
      }

      // Récupération des informations utilisateur depuis l'API
      // Cela permet de vérifier que le token est toujours valide côté serveur
      final response = await _apiService.get(
        ApiConstants.user,
        requiresAuth: true,
      );

      if (response['success'] == true) {
        _user = User.fromJson(response['data']); // Met à jour l'utilisateur
        _setLoading(false);
        notifyListeners(); // Notifie du changement d'état
        return true;       // Session valide
      } else {
        // Token invalide côté serveur, on nettoie les données locales
        await _storage.clearAll();
        _setLoading(false);
        return false;
      }
    } catch (e) {
      debugPrint('Erreur lors de la vérification : $e');
      await _storage.clearAll(); // Nettoyage en cas d'erreur
      _setLoading(false);
      return false;
    }
  }

  // ===== MÉTHODE D'INSCRIPTION =====
  // Crée un nouveau compte utilisateur
  // Retourne true en cas de succès, false en cas d'échec
  
  Future<bool> register({
    required String name,                // Nom complet de l'utilisateur
    required String email,               // Adresse email
    required String password,            // Mot de passe
    required String passwordConfirmation, // Confirmation du mot de passe
    required String role,                // Rôle dans le système
    String? telephone,                   // Numéro de téléphone (optionnel)
  }) async {
    try {
      _setLoading(true); // Active l'indicateur de chargement
      _clearError();     // Efface les erreurs précédentes

      // Appel à l'API d'inscription
      final response = await _apiService.post(
        ApiConstants.register,
        {
          'name': name,
          'email': email,
          'password': password,
          'password_confirmation': passwordConfirmation,
          'role': role,
          'telephone': telephone,
        },
      );

      if (response['success'] == true) {
        final data = response['data'];
        
        // Sauvegarde du token JWT
        await _storage.saveToken(data['access_token']);
        
        // Sauvegarde des informations utilisateur
        final userData = data['user'];
        await _storage.saveUserInfo(
          userId: userData['id'],
          role: userData['role'],
          name: userData['name'],
          email: userData['email'],
        );
        
        _user = User.fromJson(userData); // Crée l'objet User
        
        _setLoading(false);
        notifyListeners(); // Notifie du changement d'état
        
        return true; // Inscription réussie
      } else {
        throw Exception('Échec de l\'inscription');
      }
    } catch (e) {
      _setError(e.toString()); // Affiche l'erreur à l'utilisateur
      _setLoading(false);     // Désactive l'indicateur de chargement
      return false;           // Inscription échouée
    }
  }

  // ===== MÉTHODES PRIVÉES DE GESTION D'ÉTAT =====
  
  // Met à jour l'état de chargement et notifie les widgets
  void _setLoading(bool value) {
    _isLoading = value;
    notifyListeners();
  }

  // Définit un message d'erreur et notifie les widgets
  void _setError(String message) {
    // Nettoie le message en retirant le préfixe "Exception: " si présent
    _errorMessage = message.replaceAll('Exception: ', '');
    notifyListeners();
  }

  // Efface le message d'erreur courant
  void _clearError() {
    _errorMessage = null;
  }
}
