import 'environment.dart';

Future initialization() async {
  try {
    Environment.debug("calling initialize on cache");
    await Environment.instance.cache.initialize();
    Environment.debug("calling initialize on environment");
    await Environment.instance.initialize();
    // do not await this, just let it load
    Environment.instance.statusProvider.loadStatus();
    Environment.debug("end of initialization");
  } on Exception catch (e) {
    Environment.debug("caught exception during initialization: ${e.toString()}");
    // pass, do not allow exceptions to block the application
  }
}
