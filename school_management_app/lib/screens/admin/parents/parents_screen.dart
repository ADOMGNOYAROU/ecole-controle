import 'dart:io';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'add_parent_dialog.dart';
import '../../../../models/parent_model.dart';

class ParentsScreen extends StatefulWidget {
  const ParentsScreen({super.key});

  @override
  State<ParentsScreen> createState() => _ParentsScreenState();
}

class _ParentsScreenState extends State<ParentsScreen> {
  final List<Parent> _parents = [];

  void _addNewParent(String nom, String prenom, String email, String telephone,
      String adresse, String? profession, String? photoPath) {
    setState(() {
      _parents.add(
        Parent(
          id: DateTime.now().millisecondsSinceEpoch.toString(),
          nom: nom,
          prenom: prenom,
          email: email,
          telephone: telephone,
          adresse: adresse,
          profession: profession,
          photoUrl: photoPath,
        ),
      );
    });

    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Parent ajouté avec succès'),
          backgroundColor: Colors.green,
        ),
      );
    }
  }

  void _showAddParentDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return AddParentDialog(
          onAddParent: _addNewParent,
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Gestion des Parents',
          style: GoogleFonts.poppins(
            color: Colors.white,
            fontWeight: FontWeight.w600,
          ),
        ),
        backgroundColor: Colors.purple,
      ),
      body: _parents.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.family_restroom,
                    size: 80,
                    color: Colors.grey[400],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Aucun parent enregistré',
                    style: GoogleFonts.poppins(
                      color: Colors.grey[600],
                      fontSize: 18,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Cliquez sur le bouton + pour ajouter un parent',
                    style: TextStyle(color: Colors.grey[600]),
                  ),
                ],
              ),
            )
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: _parents.length,
              itemBuilder: (context, index) {
                final parent = _parents[index];
                return Card(
                  elevation: 2,
                  margin: const EdgeInsets.only(bottom: 12),
                  child: ListTile(
                    contentPadding: const EdgeInsets.all(12),
                    leading: CircleAvatar(
                      radius: 30,
                      backgroundColor: Colors.purple.shade100,
                      backgroundImage: parent.photoUrl != null
                          ? FileImage(File(parent.photoUrl!)) as ImageProvider
                          : null,
                      child: parent.photoUrl == null
                          ? Text(
                              parent.prenom[0].toUpperCase(),
                              style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.bold,
                                color: Colors.purple,
                              ),
                            )
                          : null,
                    ),
                    title: Text(
                      '${parent.prenom} ${parent.nom}',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const SizedBox(height: 4),
                        Text(
                          parent.email,
                          style: TextStyle(color: Colors.grey[700]),
                        ),
                        if (parent.profession != null) ...[
                          const SizedBox(height: 2),
                          Text(
                            parent.profession!,
                            style: TextStyle(
                              color: Colors.grey[600],
                              fontStyle: FontStyle.italic,
                            ),
                          ),
                        ],
                      ],
                    ),
                    trailing: IconButton(
                      icon: const Icon(Icons.delete, color: Colors.red),
                      onPressed: () {
                        setState(() {
                          _parents.removeAt(index);
                        });
                        if (mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Parent supprimé'),
                              backgroundColor: Colors.red,
                            ),
                          );
                        }
                      },
                    ),
                    onTap: () {
                      // Navigation vers les détails du parent
                    },
                  ),
                );
              },
            ),
      floatingActionButton: FloatingActionButton(
        onPressed: _showAddParentDialog,
        backgroundColor: Colors.purple,
        child: const Icon(Icons.add, color: Colors.white, size: 28),
      ),
    );
  }
}
