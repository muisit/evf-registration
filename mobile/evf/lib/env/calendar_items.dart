import 'package:evf/models/calendar.dart';

List<Calendar> calendarItems() {
  final List<Calendar> retval = [];

  retval.add(Calendar('id1', 'EVF Circuit', 'Mens Epee/Womens Epee', 'Türku', 'Finland', 'http://www.example.com/1',
      'http://www.feed.com/1', DateTime(2024, 4, 29), DateTime(2024, 4, 29), DateTime(2024, 4, 1)));
  retval.add(Calendar('id2', 'EVF Team Championships', 'All weapons', 'Ciney', 'Belgium', 'http://www.example.com/3',
      'http://www.feed.com/2', DateTime(2024, 5, 8), DateTime(2024, 5, 12), DateTime(2024, 4, 1)));
  retval.add(Calendar('id3', 'EVF Circuit', 'Mens/Womens Epee and Sabre', 'Graz', 'Austria', 'http://www.example.com/4',
      'http://www.feed.com/3', DateTime(2024, 6, 17), DateTime(2024, 6, 18), DateTime(2024, 4, 1)));
  retval.add(Calendar('id4', 'EVF Circuit', 'Foil and Sabre', 'Madrid', 'Spain', 'http://www.example.com/5',
      'http://www.feed.com/4', DateTime(2024, 9, 14), DateTime(2024, 9, 15), DateTime(2024, 4, 1)));
  retval.add(Calendar('id5', 'EVF Circuit', 'Sabre and Epee', 'Plovdiv', 'Bulgaria', 'http://www.example.com/6',
      'http://www.feed.com/5', DateTime(2024, 11, 29), DateTime(2024, 12, 1), DateTime(2024, 4, 1)));
  retval.add(Calendar('id6', 'EVF Circuit', 'Foil and Sabre', 'Fâches', 'France', 'http://www.example.com/7',
      'http://www.feed.com/6', DateTime(2024, 11, 12), DateTime(2024, 11, 12), DateTime(2024, 4, 1)));
  return retval;
}
