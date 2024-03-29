import 'dart:developer';
import 'package:evf/widgets/components/evf_alert_dialog.dart';
import 'package:restart_app/restart_app.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:evf/initialization.dart';
import 'package:evf/providers/status_provider.dart';
import 'package:evf/providers/feed_provider.dart';
import 'package:evf/cache/file_cache.dart';
import 'package:evf/models/flavor.dart';
import 'package:evf/models/device.dart';
import 'package:evf/api/register_device.dart';

class Environment {
  static Environment? _instance;

  Flavor flavor;
  FileCache cache;

  Environment({required this.flavor})
      : cache = FileCache(),
        device = Device(id: ''),
        authToken = '',
        feedProvider = FeedProvider(),
        statusProvider = StatusProvider() {
    Environment._instance = this;
  }

  static void debug(String txt) {
    log(txt);
  }

  static void error(String txt) {
    EvfAlertDialog.show(txt);
  }

  Future restart() async {
    await initialization();
    Restart.restartApp();
  }

  // general configuration uses public members
  Device device;
  String authToken;
  FeedProvider feedProvider;
  StatusProvider statusProvider;

  // convenience methods, only callable after initialization
  static Environment get instance => Environment._instance!;

  // implementation to persist simple local values
  SharedPreferences? _prefs;

  Future<String> preference(String key) async {
    return _prefs!.getString(key) ?? '';
  }

  Future<int> preferenceInt(String key) async {
    return _prefs!.getInt(key) ?? 0;
  }

  Future set(String key, String value) async {
    await _prefs!.setString(key, value);
  }

  Future setInt(String key, int value) async {
    await _prefs!.setInt(key, value);
  }

  Future remove(String key) async {
    await _prefs!.remove(key);
  }

  Future initialize() async {
    debug("Initializing environment");
    _prefs = await SharedPreferences.getInstance();
    // Load the device ID, if any
    debug("registering device");
    var deviceId = await preference('deviceid');
    if (deviceId == '') {
      var device = await registerDeviceAndConvert();
      await set('deviceid', device.deviceId);
    } else {
      debug("found existing device id $deviceId");
    }
    debug("setting authToken to ${device.deviceId}");
    authToken = device.deviceId;
    debug("end of environment initialization");
  }
}

Future<Device> registerDeviceAndConvert() async {
  Environment.debug("registerDeviceAndConvert");
  var device = await registerDevice();
  Environment.debug("returning json-encoded device");
  return device;
}
