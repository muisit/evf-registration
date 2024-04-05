class RankingPosition {
  int position;
  String lastName;
  String firstName;
  String country;
  double points;
  String id;

  RankingPosition(this.id, this.position, this.lastName, this.firstName, this.country, this.points);

  RankingPosition.fromJson(Map<String, dynamic> doc)
      : id = doc['id'] as String,
        lastName = doc['lastName'] as String,
        firstName = doc['firstName'] as String,
        country = doc['country'] as String,
        position = doc['position'] as int,
        points = doc['points'] as double;

  Map<String, dynamic> toJson() => {
        'id': id,
        'lastName': lastName,
        'firstName': firstName,
        'country': country,
        'position': position,
        'points': points
      };
}
