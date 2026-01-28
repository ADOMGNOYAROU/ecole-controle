import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'add_class_dialog.dart';

class ClassesScreen extends StatefulWidget {
  const ClassesScreen({super.key});

  @override
  State<ClassesScreen> createState() => _ClassesScreenState();
}

class _ClassesScreenState extends State<ClassesScreen> {
  final List<Map<String, String>> _classes = [];

  void _addNewClass(String name, String level, String teacher) {
    setState(() {
      _classes.add({
        'name': name,
        'level': level,
        'teacher': teacher,
        'id': DateTime.now().toString(),
      });
    });
    
    // Afficher un message de succès
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Classe ajoutée avec succès'),
        backgroundColor: Colors.green,
      ),
    );
  }

  void _showAddClassDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AddClassDialog(
          onAddClass: (name, level, teacher) {
            _addNewClass(name, level, teacher);
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
          'Gestion des Classes',
          style: GoogleFonts.poppins(
            color: Colors.white,
            fontWeight: FontWeight.w600,
          ),
        ),
        backgroundColor: Colors.green,
      ),
      body: _classes.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(
                    Icons.class_,
                    size: 64,
                    color: Colors.grey,
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Aucune classe enregistrée',
                    style: GoogleFonts.poppins(
                      color: Colors.grey,
                      fontSize: 18,
                    ),
                  ),
                  const SizedBox(height: 8),
                  const Text('Cliquez sur le bouton + pour ajouter une classe'),
                ],
              ),
            )
          : ListView.builder(
              itemCount: _classes.length,
              itemBuilder: (context, index) {
                final classe = _classes[index];
                return Card(
                  margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: ListTile(
                    leading: const Icon(Icons.class_, color: Colors.green),
                    title: Text(
                      '${classe['name']} - ${classe['level']}',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    subtitle: Text('Professeur: ${classe['teacher']}'),
                    trailing: IconButton(
                      icon: const Icon(Icons.delete, color: Colors.red),
                      onPressed: () {
                        setState(() {
                          _classes.removeAt(index);
                        });
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Classe supprimée'),
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
        onPressed: _showAddClassDialog,
        backgroundColor: Colors.green,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }
}
