import 'package:evf/environment.dart';
import 'package:evf/models/calendar.dart';
import 'package:evf/styles.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:flutter_gen/gen_l10n/app_localizations.dart';

typedef FeedCallBack = void Function();

class ResultsFeedButton extends StatelessWidget {
  final String label;
  final FeedCallBack callback;
  const ResultsFeedButton({super.key, required this.label, required this.callback});

  @override
  Widget build(BuildContext context) {
    return Container(
        height: 20,
        width: 55,
        child: ElevatedButton(
          onPressed: callback,
          style: ButtonStyle(
              backgroundColor: MaterialStateProperty.all(Theme.of(context).colorScheme.primary),
              foregroundColor: MaterialStateProperty.all(Theme.of(context).colorScheme.onPrimary),
              shape: MaterialStateProperty.all(
                RoundedRectangleBorder(
                  borderRadius: BorderRadius.all(
                    Radius.circular(0.5),
                  ),
                ),
              ),
              padding: MaterialStateProperty.all(EdgeInsets.fromLTRB(0, 0, 0, 0))),
          child: Text(label, style: AppStyles.feedButton),
        ));
  }
}
