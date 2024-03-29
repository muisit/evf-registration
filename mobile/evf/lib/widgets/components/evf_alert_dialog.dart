import 'dart:developer';
import 'package:flutter/material.dart';

class EvfAlertDialog {
  static GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  static void show(String error) {
    final context = navigatorKey.currentContext;

    if (context == null) {
      log('NavigatorKey context is null for $error');
      return;
    }

    try {
      final overlay = Overlay.of(context);
      OverlayEntry? overlayEntry;
      overlayEntry = OverlayEntry(
        builder: (context) => Positioned.fill(
          child: Material(
            color: Colors.black54,
            child: Center(
              child: AlertDialog(
                title: const Text('Error'),
                content: Text(error.toString()),
                actions: <Widget>[
                  TextButton(
                    child: const Text('OK'),
                    onPressed: () {
                      overlayEntry!.remove();
                    },
                  ),
                ],
              ),
            ),
          ),
        ),
      );
      overlay.insert(overlayEntry);
    } catch (e) {
      // no use catching errors when we cannot display the dialog
    }
  }
}
