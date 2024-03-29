import 'dart:developer';
import 'package:flutter/material.dart';
import 'package:flutter_gen/gen_l10n/app_localizations.dart';
import 'app_logo.dart';
import 'evf_navigation_bar.dart';
import 'pages/pages.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int currentPageIndex = 0;

  @override
  Widget build(BuildContext context) {
    onDestinationSelected(int index) {
      setState(() {
        log('switching to $index');
        currentPageIndex = index;
      });
    }

    Widget page;
    switch (currentPageIndex) {
      case 0:
        page = const FeedPage();
        break;
      case 1:
        page = const RankPage();
        break;
      case 2:
        page = const ResultsPage();
        break;
      case 3:
        page = const CalendarPage();
        break;
      case 4:
        page = const AccountPage();
        break;
      default:
        throw UnimplementedError(AppLocalizations.of(context)!.errorNoPageWidget(currentPageIndex));
    }

    return Scaffold(
      appBar: AppBar(
        backgroundColor: Theme.of(context).colorScheme.primary,
        title: const AppLogo(),
      ),
      body: Center(
          child: Container(
        color: Theme.of(context).colorScheme.primary,
        child: Container(
            decoration: BoxDecoration(
              color: const Color.fromARGB(255, 255, 255, 255),
              border: Border.all(width: 5, color: Theme.of(context).colorScheme.primary),
              borderRadius: BorderRadius.circular(12),
            ),
            margin: const EdgeInsets.all(0),
            child: Center(child: page)),
      )),
      bottomNavigationBar: EVFNavigationBar(
        currentPageIndex: currentPageIndex,
        callback: onDestinationSelected,
      ),
    );
  }
}
