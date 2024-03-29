import 'package:evf/api/get_status.dart';
import 'package:evf/environment.dart';
import 'package:evf/models/status.dart';

class StatusProvider {
  Status? status;
  bool isLoading = false;
  DateTime? wasLoaded;

  Future<Status> loadStatus() async {
    status ??= Status();
    if (!isLoading) {
      isLoading = true;
      try {
        status = await getStatus();
      } catch (e) {
        Environment.error("Could not read back end status");
      }
      isLoading = false;
      wasLoaded = DateTime.now();
    }
    // return the old value while we are not loading, or when we finished loading
    return Future.value(status);
  }
}
