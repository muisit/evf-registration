import 'package:flutter/material.dart';
import 'package:flutter_gen/gen_l10n/app_localizations.dart';

typedef EVFNavigationBarCallback = void Function(int);

class EVFNavigationBar extends StatelessWidget {
  final EVFNavigationBarCallback callback;
  final int currentPageIndex;
  const EVFNavigationBar({
    super.key,
    required this.currentPageIndex,
    required this.callback,
  });

  @override
  Widget build(BuildContext context) {
    return NavigationBar(
      onDestinationSelected: callback,
      selectedIndex: currentPageIndex,
      destinations: <Widget>[
        NavigationDestination(
          selectedIcon: const Icon(Icons.home),
          icon: const Icon(Icons.home_outlined),
          label: AppLocalizations.of(context)!.navHome,
        ),
        NavigationDestination(
          selectedIcon: const Icon(Icons.emoji_events),
          icon: const Icon(Icons.emoji_events_outlined),
          label: AppLocalizations.of(context)!.navRanking,
        ),
        NavigationDestination(
          selectedIcon: const Icon(Icons.equalizer),
          icon: const Icon(Icons.equalizer_outlined),
          label: AppLocalizations.of(context)!.navResults,
        ),
        NavigationDestination(
          selectedIcon: const Icon(Icons.calendar_today),
          icon: const Icon(Icons.calendar_today_outlined),
          label: AppLocalizations.of(context)!.navCalendar,
        ),
        NavigationDestination(
          selectedIcon: const Icon(Icons.account_circle),
          icon: const Icon(Icons.account_circle_outlined),
          label: AppLocalizations.of(context)!.navAccount,
        ),
      ],
    );
  }
}
