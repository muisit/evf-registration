import 'package:evf/models/ranking.dart';
import 'package:evf/models/ranking_position.dart';

List<Ranking> rankingItems() {
  final List<Ranking> retval = [];

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 1', 'Mens Foil', [
    RankingPosition('id1', 1, 'TEST', 'Pete', 'FRA', 120.2),
    RankingPosition('id2', 1, 'D\'TEST', 'John', 'GER', 99.8),
    RankingPosition('id3', 1, 'MUCHA', 'Test', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 2', 'Mens Foil', [
    RankingPosition('id11', 1, 'VERSUCHER', 'Trythis', 'FRA', 120.2),
    RankingPosition('id12', 1, 'MILES', 'Ahead', 'GER', 99.8),
    RankingPosition('id13', 1, 'AWAY', 'Gone', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 3', 'Mens Foil', [
    RankingPosition('id21', 1, 'YATHINK', 'Think', 'FRA', 120.2),
    RankingPosition('id22', 1, 'DEMO', 'Demo', 'GER', 99.8),
    RankingPosition('id23', 1, 'TESTA', 'Duller', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 4', 'Mens Foil', [
    RankingPosition('id31', 1, 'TESTER2', 'Jozeph', 'FRA', 120.2),
    RankingPosition('id32', 1, 'PEET', 'Erembald', 'GER', 99.8),
    RankingPosition('id33', 1, 'POOT', 'Zacharias', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 1', 'Womens Foil', [
    RankingPosition('id41', 1, 'THE POOH', 'Winnie', 'FRA', 120.2),
    RankingPosition('id42', 1, 'STARDUST', 'Louisa', 'GER', 99.8),
    RankingPosition('id43', 1, 'MONTSERAT', 'Annefrid', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 2', 'Womens Foil', [
    RankingPosition('id51', 1, 'VON ZUCKERBERG ZUM HOLESCHWANS', 'Paula', 'FRA', 120.2),
    RankingPosition('id52', 1, 'DI PEDRO ANGELUCCA ANTONIO', 'Anna', 'GER', 99.8),
    RankingPosition('id53', 1, 'KIM', 'Kim', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 3', 'Womens Foil', [
    RankingPosition('id61', 1, 'KONG', 'Li', 'FRA', 120.2),
    RankingPosition('id62', 1, 'FELIX', 'Anne', 'GER', 99.8),
    RankingPosition('id63', 1, 'ANTONIUS', 'Maria', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 4', 'Womens Foil', [
    RankingPosition('id71', 1, 'FREEP', 'Big', 'FRA', 120.2),
    RankingPosition('id72', 1, 'FREEP', 'Middle', 'GER', 99.8),
    RankingPosition('id73', 1, 'FREEP', 'Little', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 1', 'Mens Epee', [
    RankingPosition('id81', 1, 'FROOP', 'Froupé', 'FRA', 120.2),
    RankingPosition('id82', 1, 'FRAP', 'Frapper', 'GER', 99.8),
    RankingPosition('id83', 1, 'FRAPPER', 'Frappie', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 2', 'Mens Epee', [
    RankingPosition('id91', 1, 'D\'ASCOGNE', 'Mosqueteer', 'FRA', 120.2),
    RankingPosition('id92', 1, 'D\'HIVER', 'Spring', 'GER', 99.8),
    RankingPosition('id93', 1, 'BOND', 'James', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 3', 'Mens Epee', [
    RankingPosition('id101', 1, 'YESSICA', 'Marcus', 'FRA', 120.2),
    RankingPosition('id102', 1, 'ANTONIA', 'Jesus', 'GER', 99.8),
    RankingPosition('id103', 1, 'MARCELINO', 'Nero', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 4', 'Mens Epee', [
    RankingPosition('id111', 1, 'JANSEN', 'Francis', 'FRA', 120.2),
    RankingPosition('id112', 1, 'JANSSEN', 'Gustav', 'GER', 99.8),
    RankingPosition('id113', 1, 'JANSENS', 'Karl', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 1', 'Womens Epee', [
    RankingPosition('id121', 1, 'HENDRIKSDOTTIR', 'Margereth', 'FRA', 120.2),
    RankingPosition('id122', 1, 'JANSDOTTIR', 'Magdalena', 'GER', 99.8),
    RankingPosition('id123', 1, 'BJORNSDOTTIR', 'Alice', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 2', 'Womens Epee', [
    RankingPosition('id131', 1, 'PALOWNA', 'Elisabeth', 'FRA', 120.2),
    RankingPosition('id132', 1, 'THE GREAT', 'Antoinette', 'GER', 99.8),
    RankingPosition('id133', 1, 'THE RED', 'Veronica', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 3', 'Womens Epee', [
    RankingPosition('id141', 1, 'DI ORANGINA', 'Bernadette', 'FRA', 120.2),
    RankingPosition('id142', 1, 'CUPPOLA', 'Jocelyn', 'GER', 99.8),
    RankingPosition('id143', 1, 'CHIAPOLINI', 'Michelle', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 4', 'Womens Epee', [
    RankingPosition('id151', 1, 'ARMADA', 'Marcella', 'FRA', 120.2),
    RankingPosition('id152', 1, 'LOVATINO', 'Jacobina', 'GER', 99.8),
    RankingPosition('id153', 1, 'CSAR', 'Francesca', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 1', 'Mens Sabre', [
    RankingPosition('id161', 1, 'WEDGER', 'Paul', 'FRA', 120.2),
    RankingPosition('id162', 1, 'THATCHER', 'Marcus', 'GER', 99.8),
    RankingPosition('id163', 1, 'MAYOR', 'Attila', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 2', 'Mens Sabre', [
    RankingPosition('id171', 1, 'TESTALOT', 'Pjotr', 'FRA', 120.2),
    RankingPosition('id172', 1, 'CAMELOT', 'Pavlov', 'GER', 99.8),
    RankingPosition('id173', 1, 'CRYALOT', 'Kristian', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 3', 'Mens Sabre', [
    RankingPosition('id181', 1, 'NOTENOUGH', 'Chris', 'FRA', 120.2),
    RankingPosition('id182', 1, 'ANOTHERTEST', 'Dennis', 'GER', 99.8),
    RankingPosition('id183', 1, 'MORETESTA', 'Billy', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 4', 'Mens Sabre', [
    RankingPosition('id191', 1, 'ALLTHETESTS', 'Peter', 'FRA', 120.2),
    RankingPosition('id192', 1, 'NOUGHTEST', 'Zacharias', 'GER', 99.8),
    RankingPosition('id193', 1, 'NOUGATEST', 'Charles', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 1', 'Womens Sabre', [
    RankingPosition('id201', 1, 'ALLMOSTTHERE', 'Maria', 'FRA', 120.2),
    RankingPosition('id202', 1, 'NOTTHEREYET', 'Maria', 'GER', 99.8),
    RankingPosition('id203', 1, 'LASTONE', 'Maria', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 2', 'Womens Sabre', [
    RankingPosition('id211', 1, 'EVENMORE', 'Stephany', 'FRA', 120.2),
    RankingPosition('id212', 1, 'EVERMORE', 'Alice', 'GER', 99.8),
    RankingPosition('id213', 1, 'D\'ORANGE', 'Saskia', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 3', 'Womens Sabre', [
    RankingPosition('id221', 1, 'VON PHILLESTEIN', 'Klara', 'FRA', 120.2),
    RankingPosition('id222', 1, 'DURCHMACHER', 'Hilde', 'GER', 99.8),
    RankingPosition('id223', 1, 'VERURSACHER', 'Anna', 'NED', 1),
  ]));

  retval.add(Ranking(DateTime(2024, 1, 1), DateTime(2024, 3, 31), 'Cat 4', 'Womens Sabre', [
    RankingPosition('id231', 1, 'YEOLDIE', 'Petra', 'FRA', 120.2),
    RankingPosition('id232', 1, 'YEVERYOLDIE', 'Carola', 'GER', 99.8),
    RankingPosition('id233', 1, 'YEOLDEST', 'Marina', 'NED', 1),
  ]));

  return retval;
}
