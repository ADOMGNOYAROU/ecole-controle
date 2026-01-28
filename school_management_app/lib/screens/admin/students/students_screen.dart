import 'dart:io';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'add_student_dialog.dart';
import '../../../../models/student_model.dart';

class StudentsScreen extends StatefulWidget {
  const StudentsScreen({super.key});

  @override
  State<StudentsScreen> createState() => _StudentsScreenState();
}

class _StudentsScreenState extends State<StudentsScreen> {
  final List<Student> _students = [];
  final TextEditingController _searchController = TextEditingController();
  String _selectedClass = 'Toutes les classes';
  bool _isLoading = true;
  
  // Liste des classes pour le filtre
  final List<String> _classes = [
    'Toutes les classes',
    '6ème A',
    '5ème B',
    '4ème C',
    '3ème D',
  ];

  @override
  void initState() {
    super.initState();
    // Simuler un chargement de données
    _loadStudents();
  }
  
  Future<void> _loadStudents() async {
    // Simuler un chargement de données depuis une API
    await Future.delayed(const Duration(seconds: 1));
    
    // Données factices pour l'exemple
    final mockStudents = [
      Student(
        id: '1',
        firstName: 'Jean',
        lastName: 'Dupont',
        dateOfBirth: DateTime(2012, 5, 15),
        gender: 'Masculin',
        address: '123 Rue de l\'École, 75000 Paris',
        phoneNumber: '0612345678',
        email: 'jean.dupont@email.com',
        parentId: '1',
        classId: '1',
        photoUrl: null,
        bloodGroup: 'A+',
        allergies: 'Aucune',
        medicalNotes: 'Porte des lunettes',
      ),
      Student(
        id: '2',
        firstName: 'Marie',
        lastName: 'Martin',
        dateOfBirth: DateTime(2011, 8, 22),
        gender: 'Féminin',
        address: '456 Avenue des Fleurs, 75000 Paris',
        phoneNumber: '0698765432',
        email: 'marie.martin@email.com',
        parentId: '2',
        classId: '2',
        photoUrl: null,
        bloodGroup: 'O+',
        allergies: 'Arachides',
        medicalNotes: 'Allergie sévère aux arachides',
      ),
    ];
    
    if (mounted) {
      setState(() {
        _students.addAll(mockStudents);
        _isLoading = false;
      });
    }
  }
  
  // Filtrer les étudiants selon la recherche et la classe sélectionnée
  List<Student> get _filteredStudents {
    final searchQuery = _searchController.text.toLowerCase();
    return _students.where((student) {
      final matchesSearch = searchQuery.isEmpty || 
          '${student.firstName} ${student.lastName}'.toLowerCase().contains(searchQuery) ||
          student.email?.toLowerCase().contains(searchQuery) == true;
      
      final matchesClass = _selectedClass == 'Toutes les classes' || 
          _classes[int.tryParse(student.classId) ?? 0] == _selectedClass;
      
      return matchesSearch && matchesClass;
    }).toList();
  }

  void _addNewStudent(
    String firstName,
    String lastName,
    DateTime dateOfBirth,
    String gender,
    String address,
    String phoneNumber,
    String? email,
    String parentId,
    String classId,
    String? photoPath,
    String? bloodGroup,
    String? allergies,
    String? medicalNotes,
  ) {
    final newStudent = Student(
      id: DateTime.now().millisecondsSinceEpoch.toString(),
      firstName: firstName,
      lastName: lastName,
      dateOfBirth: dateOfBirth,
      gender: gender,
      address: address,
      phoneNumber: phoneNumber,
      email: email,
      parentId: parentId,
      classId: classId,
      photoUrl: photoPath,
      bloodGroup: bloodGroup,
      allergies: allergies,
      medicalNotes: medicalNotes,
    );
    
    setState(() {
      _students.add(newStudent);
    });
    
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('$firstName $lastName a été ajouté(e) avec succès'),
          backgroundColor: Colors.green,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  // Afficher l'état vide
  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.school_outlined,
            size: 80,
            color: Colors.grey[300],
          ),
          const SizedBox(height: 16),
          Text(
            'Aucun étudiant trouvé',
            style: GoogleFonts.poppins(
              fontSize: 18,
              color: Colors.grey,
              fontWeight: FontWeight.w500,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            _searchController.text.isNotEmpty || _selectedClass != 'Toutes les classes'
                ? 'Aucun étudiant ne correspond à votre recherche.'
                : 'Commencez par ajouter un nouvel étudiant',
            style: TextStyle(color: Colors.grey[500]),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          if (_searchController.text.isNotEmpty || _selectedClass != 'Toutes les classes')
            ElevatedButton(
              onPressed: () {
                setState(() {
                  _searchController.clear();
                  _selectedClass = 'Toutes les classes';
                });
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.blue,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
              child: const Text('Réinitialiser les filtres'),
            )
          else
            ElevatedButton.icon(
              onPressed: _showAddStudentDialog,
              icon: const Icon(Icons.person_add, size: 18),
              label: const Text('Ajouter un étudiant'),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.blue,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8),
                ),
              ),
            ),
        ],
      ),
    );
  }
  
  // Construire une carte d'étudiant
  Widget _buildStudentCard(Student student, int index) {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      elevation: 1,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      child: InkWell(
        onTap: () => _showStudentDetails(student),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            children: [
              // Photo de profil
              Hero(
                tag: 'student-${student.id}',
                child: CircleAvatar(
                  radius: 30,
                  backgroundColor: Colors.blue.shade50,
                  backgroundImage: student.photoUrl != null
                      ? FileImage(File(student.photoUrl!)) as ImageProvider
                      : null,
                  child: student.photoUrl == null
                      ? Text(
                          '${student.firstName[0]}${student.lastName[0]}',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: Colors.blue.shade700,
                          ),
                        )
                      : null,
                ),
              ),
              const SizedBox(width: 16),
              // Détails de l'étudiant
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      '${student.firstName} ${student.lastName}',
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(
                          Icons.class_,
                          size: 16,
                          color: Colors.grey[600],
                        ),
                        const SizedBox(width: 4),
                        Text(
                          _classes[int.tryParse(student.classId) ?? 0],
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 2),
                    Row(
                      children: [
                        Icon(
                          Icons.cake,
                          size: 16,
                          color: Colors.grey[600],
                        ),
                        const SizedBox(width: 4),
                        Text(
                          '${student.age} ans',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              // Boutons d'action
              PopupMenuButton<String>(
                icon: const Icon(Icons.more_vert, color: Colors.grey),
                onSelected: (value) {
                  if (value == 'edit') {
                    // TODO: Implémenter l'édition
                  } else if (value == 'delete') {
                    _confirmDeleteStudent(student, _students.indexOf(student));
                  } else if (value == 'view') {
                    _showStudentDetails(student);
                  }
                },
                itemBuilder: (BuildContext context) => [
                  const PopupMenuItem(
                    value: 'view',
                    child: Row(
                      children: [
                        Icon(Icons.visibility, size: 20, color: Colors.blue),
                        SizedBox(width: 8),
                        Text('Voir détails'),
                      ],
                    ),
                  ),
                  const PopupMenuItem(
                    value: 'edit',
                    child: Row(
                      children: [
                        Icon(Icons.edit, size: 20, color: Colors.orange),
                        SizedBox(width: 8),
                        Text('Modifier'),
                      ],
                    ),
                  ),
                  const PopupMenuItem(
                    value: 'delete',
                    child: Row(
                      children: [
                        Icon(Icons.delete, size: 20, color: Colors.red),
                        SizedBox(width: 8),
                        Text('Supprimer'),
                      ],
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  // Afficher le dialogue d'ajout d'étudiant
  void _showAddStudentDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AddStudentDialog(
          onAddStudent: (
            String firstName,
            String lastName,
            DateTime dateOfBirth,
            String gender,
            String address,
            String phoneNumber,
            String? email,
            String parentId,
            String classId,
            String? photoPath,
            String? bloodGroup,
            String? allergies,
            String? medicalNotes,
          ) {
            _addNewStudent(
              firstName,
              lastName,
              dateOfBirth,
              gender,
              address,
              phoneNumber,
              email,
              parentId,
              classId,
              photoPath,
              bloodGroup,
              allergies,
              medicalNotes,
            );
          },
        );
      },
    );
  }

  // Afficher les détails d'un étudiant
  void _showStudentDetails(Student student) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(
          'Détails de l\'étudiant',
          style: GoogleFonts.poppins(
            fontWeight: FontWeight.w600,
          ),
        ),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Center(
                child: CircleAvatar(
                  radius: 50,
                  backgroundColor: Colors.blue.shade100,
                  backgroundImage: student.photoUrl != null
                      ? FileImage(File(student.photoUrl!)) as ImageProvider
                      : null,
                  child: student.photoUrl == null
                      ? Text(
                          '${student.firstName[0]}${student.lastName[0]}',
                          style: const TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                            color: Colors.blue,
                          ),
                        )
                      : null,
                ),
              ),
              const SizedBox(height: 16),
              _buildDetailRow('Nom complet', '${student.firstName} ${student.lastName}'),
              _buildDetailRow('Classe', _classes[int.tryParse(student.classId) ?? 0]),
              _buildDetailRow('Date de naissance', 
                  DateFormat('dd/MM/yyyy').format(student.dateOfBirth)),
              _buildDetailRow('Âge', '${student.age} ans'),
              _buildDetailRow('Genre', student.gender),
              _buildDetailRow('Téléphone', student.phoneNumber),
              if (student.email?.isNotEmpty ?? false)
                _buildDetailRow('Email', student.email!),
              _buildDetailRow('Adresse', student.address),
              if (student.bloodGroup?.isNotEmpty ?? false)
                _buildDetailRow('Groupe sanguin', student.bloodGroup!),
              if (student.allergies?.isNotEmpty ?? false)
                _buildDetailRow('Allergies', student.allergies!),
              if (student.medicalNotes?.isNotEmpty ?? false)
                _buildDetailRow('Notes médicales', student.medicalNotes!),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Fermer'),
          ),
        ],
      ),
    );
  }
  
  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4.0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontWeight: FontWeight.bold,
              color: Colors.grey,
              fontSize: 12,
            ),
          ),
          Text(
            value,
            style: const TextStyle(fontSize: 16),
          ),
          const Divider(),
        ],
      ),
    );
  }
  
  // Confirmer la suppression d'un étudiant
  Future<void> _confirmDeleteStudent(Student student, int index) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Confirmer la suppression'),
        content: Text(
            'Voulez-vous vraiment supprimer l\'étudiant ${student.firstName} ${student.lastName} ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Annuler'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Supprimer'),
          ),
        ],
      ),
    );
    
    if (confirmed == true) {
      _deleteStudent(student, index);
    }
  }
  
  void _deleteStudent(Student student, int index) {
    setState(() {
      _students.removeAt(index);
    });
    
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${student.firstName} ${student.lastName} a été supprimé(e)'),
          backgroundColor: Colors.red,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Gestion des Étudiants',
          style: GoogleFonts.poppins(
            color: Colors.white,
            fontWeight: FontWeight.w600,
          ),
        ),
        backgroundColor: Colors.blue,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Barre de recherche et filtres
          Container(
            padding: const EdgeInsets.all(16),
            color: Colors.grey[50],
            child: Column(
              children: [
                // Barre de recherche
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Rechercher un étudiant...',
                    prefixIcon: const Icon(Icons.search),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(10),
                      borderSide: BorderSide.none,
                    ),
                    filled: true,
                    fillColor: Colors.white,
                    contentPadding: const EdgeInsets.symmetric(vertical: 0),
                  ),
                  onChanged: (value) => setState(() {}),
                ),
                const SizedBox(height: 12),
                // Filtres
                Row(
                  children: [
                    Expanded(
                      child: DropdownButtonFormField<String>(
                        value: _selectedClass,
                        decoration: InputDecoration(
                          labelText: 'Filtrer par classe',
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 0),
                          filled: true,
                          fillColor: Colors.white,
                        ),
                        items: _classes.map((String classe) {
                          return DropdownMenuItem<String>(
                            value: classe,
                            child: Text(classe),
                          );
                        }).toList(),
                        onChanged: (String? newValue) {
                          if (newValue != null) {
                            setState(() {
                              _selectedClass = newValue;
                            });
                          }
                        },
                      ),
                    ),
                    const SizedBox(width: 10),
                    // Bouton pour réinitialiser les filtres
                    IconButton(
                      onPressed: () {
                        setState(() {
                          _searchController.clear();
                          _selectedClass = 'Toutes les classes';
                        });
                      },
                      style: IconButton.styleFrom(
                        backgroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                          side: BorderSide(color: Colors.grey[300]!),
                        ),
                      ),
                      icon: const Icon(Icons.refresh, color: Colors.blue),
                    ),
                  ],
                ),
              ],
            ),
          ),
          
          // Affichage du nombre d'étudiants
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
            alignment: Alignment.centerLeft,
            child: Text(
              '${_filteredStudents.length} étudiant${_filteredStudents.length > 1 ? 's' : ''} trouvé${_filteredStudents.length > 1 ? 's' : ''}',
              style: TextStyle(
                color: Colors.grey[600],
                fontSize: 14,
              ),
            ),
          ),
          
          // Liste des étudiants
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator())
                : _filteredStudents.isEmpty
                    ? _buildEmptyState()
                    : RefreshIndicator(
                        onRefresh: () async {
                          // Recharger les étudiants
                          setState(() => _isLoading = true);
                          await _loadStudents();
                        },
                        child: ListView.builder(
                          padding: const EdgeInsets.only(bottom: 80),
                          itemCount: _filteredStudents.length,
                          itemBuilder: (context, index) {
                            final student = _filteredStudents[index];
                            return _buildStudentCard(student, index);
                          },
                        ),
                      ),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _showAddStudentDialog,
        backgroundColor: Colors.blue,
        icon: const Icon(Icons.person_add, color: Colors.white),
        label: const Text('Ajouter', style: TextStyle(color: Colors.white)),
      ),
    );
  }
}
