class Fencer {
  String id;
  String lastName;
  String firstName;
  String country;

  Fencer()
      : id = '',
        lastName = '',
        firstName = '',
        country = '';

  Fencer.fromJson(Map<String, dynamic> doc)
      : id = doc['id'] as String,
        lastName = doc['lastName'] as String,
        firstName = doc['firstName'] as String,
        country = doc['country'] as String;

  Map<String, dynamic> toJson() => {
        'id': id,
        'lastName': lastName,
        'firstName': firstName,
        'country': country,
      };

  String fullName() {
    return "$lastName, $firstName";
  }
}
