import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'add_teacher_dialog.dart';

class TeachersScreen extends StatefulWidget {
  const TeachersScreen({super.key});

  @override
  State<TeachersScreen> createState() => _TeachersScreenState();
}

class _TeachersScreenState extends State<TeachersScreen> {
  final List<Map<String, String>> _teachers = [];

  void _addNewTeacher(String name, String email, String phone, String subject) {
    setState(() {
      _teachers.add({
        'name': name,
        'email': email,
        'phone': phone,
        'subject': subject,
        'id': DateTime.now().toString(),
      });
    });
    
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Enseignant ajouté avec succès'),
        backgroundColor: Colors.green,
      ),
    );
  }

  void _showAddTeacherDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AddTeacherDialog(
          onAddTeacher: (name, email, phone, subject) {
            _addNewTeacher(name, email, phone, subject);
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Gestion des Enseignants',
          style: GoogleFonts.poppins(
            color: Colors.white,
            fontWeight: FontWeight.w600,
          ),
        ),
        backgroundColor: Colors.orange,
      ),
      body: _teachers.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(
                    Icons.school,
                    size: 64,
                    color: Colors.grey,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Aucun enseignant enregistré',
                    style: GoogleFonts.poppins(
                      color: Colors.grey,
                      fontSize: 18,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text('Cliquez sur le bouton + pour ajouter un enseignant'),
                ],
              ),
            )
          : ListView.builder(
              itemCount: _teachers.length,
              itemBuilder: (context, index) {
                final teacher = _teachers[index];
                return Card(
                  margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: Colors.orange.shade100,
                      child: Text(
                        teacher['name']?[0] ?? '?',
                        style: const TextStyle(color: Colors.orange),
                      ),
                    ),
                    title: Text(
                      teacher['name'] ?? '',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(teacher['email'] ?? ''),
                        if (teacher['subject']?.isNotEmpty ?? false)
                          Text('Matière: ${teacher['subject']}'),
                      ],
                    ),
                    trailing: IconButton(
                      icon: const Icon(Icons.delete, color: Colors.red),
                      onPressed: () {
                        setState(() {
                          _teachers.removeAt(index);
                        });
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Enseignant supprimé'),
                            backgroundColor: Colors.red,
                          ),
                        );
                      },
                    ),
                  ),
                );
              },
            ),
      floatingActionButton: FloatingActionButton(
        onPressed: _showAddTeacherDialog,
        backgroundColor: Colors.orange,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }
}
