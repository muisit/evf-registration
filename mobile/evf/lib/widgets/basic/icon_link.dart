import 'package:evf/environment.dart';
import 'package:evf/models/calendar.dart';
import 'package:evf/widgets/components/results_feed_button.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:flutter_gen/gen_l10n/app_localizations.dart';

class IconLink extends StatelessWidget {
  final Icon icon;
  final String url;
  const IconLink({super.key, required this.icon, required this.url});

  @override
  Widget build(BuildContext context) {
    return IconButton(icon: icon, onPressed: _pressIcon);
  }

  Future _pressIcon() async {
    try {
      await launchUrl(Uri.parse(url));
    } catch (e) {
      // skip any failures to launch the url
    }
  }
}
