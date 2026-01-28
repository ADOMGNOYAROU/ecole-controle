import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class ScheduleScreen extends StatefulWidget {
  const ScheduleScreen({super.key});

  @override
  State<ScheduleScreen> createState() => _ScheduleScreenState();
}

class _ScheduleScreenState extends State<ScheduleScreen> {
  final List<String> _days = [
    'Lundi',
    'Mardi',
    'Mercredi',
    'Jeudi',
    'Vendredi',
    'Samedi'
  ];
  final List<String> _timeSlots = [
    '08:00 - 09:30',
    '09:45 - 11:15',
    '11:30 - 13:00',
    '14:00 - 15:30',
    '15:45 - 17:15',
    '17:30 - 19:00'
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Emploi du temps',
          style: GoogleFonts.poppins(
            color: Colors.white,
            fontWeight: FontWeight.w600,
          ),
        ),
        backgroundColor: Colors.purple,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.add, color: Colors.white),
            onPressed: () {
              // Ajouter un nouveau cours
              _showAddCourseDialog();
            },
          ),
        ],
      ),
      body: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        child: SingleChildScrollView(
          child: DataTable(
            columns: [
              const DataColumn(label: Text('Heure / Jour')),
              ..._days.map((day) => DataColumn(label: Text(day))).toList(),
            ],
            rows: _timeSlots.map((timeSlot) {
              return DataRow(
                cells: [
                  DataCell(Text(timeSlot)),
                  ..._days.map((day) => DataCell(_buildCourseCell(day, timeSlot))).toList(),
                ],
              );
            }).toList(),
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: () {
          _showAddCourseDialog();
        },
        backgroundColor: Colors.purple,
        child: const Icon(Icons.add, color: Colors.white),
      ),
    );
  }

  Widget _buildCourseCell(String day, String time) {
    // Ici, vous pouvez personnaliser l'affichage des cellules en fonction de vos données
    return const SizedBox.shrink();
  }

  void _showAddCourseDialog() {
    showDialog(
      context: context,
      builder: (BuildContext context) {
        String? selectedDay;
        String? selectedTime;
        String? selectedClass;
        String? selectedSubject;
        String? selectedTeacher;

        final List<String> classes = ['6ème A', '5ème B', '4ème C', '3ème D'];
        final List<String> subjects = ['Maths', 'Français', 'Histoire', 'Géographie'];
        final List<String> teachers = ['M. Dupont', 'Mme Martin', 'M. Durand'];

        return AlertDialog(
          title: const Text('Ajouter un cours'),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                DropdownButtonFormField<String>(
                  decoration: const InputDecoration(
                    labelText: 'Jour',
                    border: OutlineInputBorder(),
                  ),
                  items: _days.map((String day) {
                    return DropdownMenuItem<String>(
                      value: day,
                      child: Text(day),
                    );
                  }).toList(),
                  onChanged: (String? value) {
                    selectedDay = value;
                  },
                  validator: (value) => value == null ? 'Champ requis' : null,
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  decoration: const InputDecoration(
                    labelText: 'Créneau horaire',
                    border: OutlineInputBorder(),
                  ),
                  items: _timeSlots.map((String time) {
                    return DropdownMenuItem<String>(
                      value: time,
                      child: Text(time),
                    );
                  }).toList(),
                  onChanged: (String? value) {
                    selectedTime = value;
                  },
                  validator: (value) => value == null ? 'Champ requis' : null,
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  decoration: const InputDecoration(
                    labelText: 'Classe',
                    border: OutlineInputBorder(),
                  ),
                  items: classes.map((String className) {
                    return DropdownMenuItem<String>(
                      value: className,
                      child: Text(className),
                    );
                  }).toList(),
                  onChanged: (String? value) {
                    selectedClass = value;
                  },
                  validator: (value) => value == null ? 'Champ requis' : null,
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  decoration: const InputDecoration(
                    labelText: 'Matière',
                    border: OutlineInputBorder(),
                  ),
                  items: subjects.map((String subject) {
                    return DropdownMenuItem<String>(
                      value: subject,
                      child: Text(subject),
                    );
                  }).toList(),
                  onChanged: (String? value) {
                    selectedSubject = value;
                  },
                  validator: (value) => value == null ? 'Champ requis' : null,
                ),
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  decoration: const InputDecoration(
                    labelText: 'Enseignant',
                    border: OutlineInputBorder(),
                  ),
                  items: teachers.map((String teacher) {
                    return DropdownMenuItem<String>(
                      value: teacher,
                      child: Text(teacher),
                    );
                  }).toList(),
                  onChanged: (String? value) {
                    selectedTeacher = value;
                  },
                  validator: (value) => value == null ? 'Champ requis' : null,
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text('Annuler'),
            ),
            ElevatedButton(
              onPressed: () {
                // Enregistrer le cours
                Navigator.of(context).pop();
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Cours ajouté avec succès'),
                    backgroundColor: Colors.green,
                  ),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.purple,
              ),
              child: const Text('Ajouter', style: TextStyle(color: Colors.white)),
            ),
          ],
        );
      },
    );
  }
}
