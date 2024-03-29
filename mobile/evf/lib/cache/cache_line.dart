class CacheLine {
  String timestamp = '';
  String path = '';

  CacheLine({required this.timestamp, required this.path});

  CacheLine.fromJson(Map<String, dynamic> values) {
    timestamp = values['ts'].toString();
    path = values['path'].toString();
  }

  Map<String, dynamic> toJson() => {'ts': timestamp, 'path': path};
}
