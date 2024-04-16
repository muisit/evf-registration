import 'package:evf/models/ranking.dart';
import 'package:evf/models/ranking_position.dart';

List<Ranking> rankingItems() {
  final List<Ranking> retval = [];

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 1', 'Mens Foil', [
    RankingPosition('ba55570a-09bb-48d8-b519-a7e4d658b45c', 1, 'TEST', 'Pete', 'FRA', 999.9),
    RankingPosition('39618535-fdc8-4824-9ca7-f770401d1292', 2, 'D\'TEST', 'John', 'GER', 99.8),
    RankingPosition('95400711-2d35-41aa-81c6-fb551e9dd3df', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('e924a123-ae47-4898-b79f-fe1ca108bef6', 3, 'MUCHA ADO ABOUT NOTHING LONG LASTNAME',
        'Test with A Very long firstname', 'NED', 1),
    RankingPosition('id5', 3, 'DAFIGUIREDODIMUNDI', 'Testmewithaanonbreakablename', 'NED', 1),
    RankingPosition('id6', 3, 'MUCHA', 'Test', 'WWW', 1),
    RankingPosition('id7', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id8', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id9', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id01', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id02', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id03', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id04', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id05', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id06', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id07', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id08', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id09', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id010', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id011', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id012', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id013', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id014', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id015', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id016', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id017', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id018', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id019', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id020', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id021', 3, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id022', 263, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id023', 273, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id024', 283, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id025', 293, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id026', 303, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id027', 313, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id028', 323, 'MUCHA', 'Test', 'NED', 1),
    RankingPosition('id029', 999, 'MUCHA', 'Test', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 2', 'Mens Foil', [
    RankingPosition('id11', 1, 'VERSUCHER', 'Trythis', 'FRA', 120.2),
    RankingPosition('id12', 2, 'MILES', 'Ahead', 'GER', 99.8),
    RankingPosition('id13', 3, 'AWAY', 'Gone', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 3', 'Mens Foil', [
    RankingPosition('id21', 1, 'YATHINK', 'Think', 'FRA', 120.2),
    RankingPosition('id22', 2, 'DEMO', 'Demo', 'GER', 99.8),
    RankingPosition('id23', 3, 'TESTA', 'Duller', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 4', 'Mens Foil', [
    RankingPosition('id31', 1, 'TESTER2', 'Jozeph', 'FRA', 120.2),
    RankingPosition('id32', 2, 'PEET', 'Erembald', 'GER', 99.8),
    RankingPosition('id33', 3, 'POOT', 'Zacharias', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 1', 'Womens Foil', [
    RankingPosition('id41', 1, 'THE POOH', 'Winnie', 'FRA', 120.2),
    RankingPosition('id42', 2, 'STARDUST', 'Louisa', 'GER', 99.8),
    RankingPosition('id43', 3, 'MONTSERAT', 'Annefrid', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 2', 'Womens Foil', [
    RankingPosition('id51', 1, 'VON ZUCKERBERG ZUM HOLESCHWANS', 'Paula', 'FRA', 120.2),
    RankingPosition('id52', 2, 'DI PEDRO ANGELUCCA ANTONIO', 'Anna', 'GER', 99.8),
    RankingPosition('id53', 3, 'KIM', 'Kim', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 3', 'Womens Foil', [
    RankingPosition('id61', 1, 'KONG', 'Li', 'FRA', 120.2),
    RankingPosition('id62', 2, 'FELIX', 'Anne', 'GER', 99.8),
    RankingPosition('id63', 3, 'ANTONIUS', 'Maria', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 4', 'Womens Foil', [
    RankingPosition('id71', 1, 'FREEP', 'Big', 'FRA', 120.2),
    RankingPosition('id72', 2, 'FREEP', 'Middle', 'GER', 99.8),
    RankingPosition('id73', 3, 'FREEP', 'Little', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 1', 'Mens Epee', [
    RankingPosition('id81', 1, 'FROOP', 'Froupé', 'FRA', 120.2),
    RankingPosition('id82', 2, 'FRAP', 'Frapper', 'GER', 99.8),
    RankingPosition('id83', 3, 'FRAPPER', 'Frappie', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 2', 'Mens Epee', [
    RankingPosition('id91', 1, 'D\'ASCOGNE', 'Mosqueteer', 'FRA', 120.2),
    RankingPosition('id92', 2, 'D\'HIVER', 'Spring', 'GER', 99.8),
    RankingPosition('id93', 3, 'BOND', 'James', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 3', 'Mens Epee', [
    RankingPosition('id101', 1, 'YESSICA', 'Marcus', 'FRA', 120.2),
    RankingPosition('id102', 2, 'ANTONIA', 'Jesus', 'GER', 99.8),
    RankingPosition('id103', 3, 'MARCELINO', 'Nero', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 4', 'Mens Epee', [
    RankingPosition('id111', 1, 'JANSEN', 'Francis', 'FRA', 120.2),
    RankingPosition('id112', 2, 'JANSSEN', 'Gustav', 'GER', 99.8),
    RankingPosition('id113', 3, 'JANSENS', 'Karl', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 1', 'Womens Epee', [
    RankingPosition('id121', 1, 'HENDRIKSDOTTIR', 'Margereth', 'FRA', 120.2),
    RankingPosition('id122', 2, 'JANSDOTTIR', 'Magdalena', 'GER', 99.8),
    RankingPosition('id123', 3, 'BJORNSDOTTIR', 'Alice', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 2', 'Womens Epee', [
    RankingPosition('id131', 1, 'PALOWNA', 'Elisabeth', 'FRA', 120.2),
    RankingPosition('id132', 2, 'THE GREAT', 'Antoinette', 'GER', 99.8),
    RankingPosition('id133', 3, 'THE RED', 'Veronica', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 3', 'Womens Epee', [
    RankingPosition('id141', 1, 'DI ORANGINA', 'Bernadette', 'FRA', 120.2),
    RankingPosition('id142', 2, 'CUPPOLA', 'Jocelyn', 'GER', 99.8),
    RankingPosition('id143', 3, 'CHIAPOLINI', 'Michelle', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 4', 'Womens Epee', [
    RankingPosition('id151', 1, 'ARMADA', 'Marcella', 'FRA', 120.2),
    RankingPosition('id152', 2, 'LOVATINO', 'Jacobina', 'GER', 99.8),
    RankingPosition('id153', 3, 'CSAR', 'Francesca', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 1', 'Mens Sabre', [
    RankingPosition('id161', 1, 'WEDGER', 'Paul', 'FRA', 120.2),
    RankingPosition('id162', 2, 'THATCHER', 'Marcus', 'GER', 99.8),
    RankingPosition('id163', 3, 'MAYOR', 'Attila', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 2', 'Mens Sabre', [
    RankingPosition('id171', 1, 'TESTALOT', 'Pjotr', 'FRA', 120.2),
    RankingPosition('id172', 2, 'CAMELOT', 'Pavlov', 'GER', 99.8),
    RankingPosition('id173', 3, 'CRYALOT', 'Kristian', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 3', 'Mens Sabre', [
    RankingPosition('id181', 1, 'NOTENOUGH', 'Chris', 'FRA', 120.2),
    RankingPosition('id182', 2, 'ANOTHERTEST', 'Dennis', 'GER', 99.8),
    RankingPosition('id183', 3, 'MORETESTA', 'Billy', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 4', 'Mens Sabre', [
    RankingPosition('id191', 1, 'ALLTHETESTS', 'Peter', 'FRA', 120.2),
    RankingPosition('id192', 2, 'NOUGHTEST', 'Zacharias', 'GER', 99.8),
    RankingPosition('id193', 3, 'NOUGATEST', 'Charles', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 1', 'Womens Sabre', [
    RankingPosition('id201', 1, 'ALLMOSTTHERE', 'Maria', 'FRA', 120.2),
    RankingPosition('id202', 2, 'NOTTHEREYET', 'Maria', 'GER', 99.8),
    RankingPosition('id203', 3, 'LASTONE', 'Maria', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 2', 'Womens Sabre', [
    RankingPosition('id211', 1, 'EVENMORE', 'Stephany', 'FRA', 120.2),
    RankingPosition('id212', 2, 'EVERMORE', 'Alice', 'GER', 99.8),
    RankingPosition('id213', 3, 'D\'ORANGE', 'Saskia', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 3', 'Womens Sabre', [
    RankingPosition('id221', 1, 'VON PHILLESTEIN', 'Klara', 'FRA', 120.2),
    RankingPosition('id222', 2, 'DURCHMACHER', 'Hilde', 'GER', 99.8),
    RankingPosition('id223', 3, 'VERURSACHER', 'Anna', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 4', 'Womens Sabre', [
    RankingPosition('id231', 1, 'YEOLDIE', 'Petra', 'FRA', 120.2),
    RankingPosition('id232', 2, 'YEVERYOLDIE', 'Carola', 'GER', 99.8),
    RankingPosition('id233', 3, 'YEOLDEST', 'Marina', 'NED', 1),
  ]));

  return retval;
}
