import 'package:evf/environment.dart';
import 'package:evf/widgets/components/evf_alert_dialog.dart';
import 'package:flutter/material.dart';
import 'package:flutter_gen/gen_l10n/app_localizations.dart';
import 'package:provider/provider.dart';
import 'package:evf/providers/feed_provider.dart';
import './home_page.dart';

class MainApp extends StatelessWidget {
  final bool doDebug;

  MainApp({super.key, required this.doDebug});

  @override
  Widget build(BuildContext context) {
    Environment.debug("setting navigatorKey on main widget");
    return MultiProvider(
        providers: [ChangeNotifierProvider<FeedProvider>(create: (context) => Environment.instance.feedProvider)],
        child: MaterialApp(
          navigatorKey: EvfAlertDialog.navigatorKey,
          debugShowCheckedModeBanner: doDebug,
          title: "MyEVF",
          theme: ThemeData(
            colorScheme: ColorScheme.fromSeed(seedColor: const Color.fromARGB(0, 0, 51, 102)),
            useMaterial3: true,
          ),
          localizationsDelegates: AppLocalizations.localizationsDelegates,
          supportedLocales: AppLocalizations.supportedLocales,
          home: const HomePage(),
        ));
  }
}
