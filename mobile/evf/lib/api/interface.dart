import 'dart:async';
import 'dart:convert';
import 'dart:core';
import 'dart:io';
import 'package:evf/api/register_device.dart';
import 'package:http/http.dart' as http;
import 'package:evf/environment.dart';

typedef NetworkCall = Future<http.Response> Function();
typedef ErrorCall = Future<Map<String, dynamic>> Function(int tries);

class Interface {
  Map<String, String> data = {};
  String path = '';

  Interface.create({required this.path, Map<String, String>? data}) {
    if (data != null) {
      this.data = data;
    }
  }

  Future<String> getRaw({
    String? path,
    Map<String, String>? data,
  }) async {
    if (path != null) {
      this.path = path;
    }
    if (data != null) {
      this.data = data;
    }
    final url = Environment.instance.flavor.apiUrl + this.path;
    Environment.debug("calling GET $url using ${Environment.instance.authToken}");
    final uri = Uri.parse(url).replace(queryParameters: this.data);
    var response = await http.get(
      uri,
      headers: {
        HttpHeaders.authorizationHeader: Environment.instance.authToken,
      },
    );
    Environment.debug("parsing get response");
    return _parseResponse(response);
  }

  Future<String> postRaw({
    String? path,
    Map<String, String>? data,
  }) async {
    if (path != null) {
      this.path = path;
    }
    if (data != null) {
      this.data = data;
    }
    final url = Environment.instance.flavor.apiUrl + this.path;
    Environment.debug("calling POST $url with data ${this.data.toString} using ${Environment.instance.authToken}");
    final uri = Uri.parse(url);
    var response = await http.post(uri,
        headers: {
          HttpHeaders.authorizationHeader: Environment.instance.authToken,
        },
        body: jsonEncode(this.data));
    Environment.debug("parsing POST response");
    return _parseResponse(response);
  }

  Future<Map<String, dynamic>> get(
      {String? path, Map<String, String>? data, bool unAuth = false, int tries = 0}) async {
    try {
      return jsonDecode(await getRaw(path: path, data: data)) as Map<String, dynamic>;
    } on NetworkError catch (e) {
      Environment.debug("caught a network error inside the api get interface ${e.code}");
      // catch all unauthorized calls. Unless we specifically indicate we may expect this, the cause
      // of this reaction is probably that the back-end does no longer recognize our device id
      // In that case, we must reinitialize and restart the app. This is cumbersome, but the alternative
      // is that we implement the re-identification here and then perform the original call.
      return await _parseError(e, tries, unAuth, (int myTries) async {
        return await get(path: path, data: data, unAuth: unAuth, tries: myTries);
      });
    }
  }

  Future<Map<String, dynamic>> post(
      {String? path, Map<String, String>? data, bool unAuth = false, int tries = 0}) async {
    try {
      return jsonDecode(await postRaw(path: path, data: data)) as Map<String, dynamic>;
    } on NetworkError catch (e) {
      Environment.debug("caught a network error inside the api post interface ${e.code}");
      // catch all unauthorized calls. Unless we specifically indicate we may expect this, the cause
      // of this reaction is probably that the back-end does no longer recognize our device id
      // In that case, we must reinitialize and restart the app. This is cumbersome, but the alternative
      // is that we implement the re-identification here and then perform the original call, i
      return await _parseError(e, tries, unAuth, (int myTries) async {
        return await post(path: path, data: data, unAuth: unAuth, tries: myTries);
      });
    }
  }

  Future<Map<String, dynamic>> _parseError(
      NetworkError e, int tries, bool expectUnauthenticated, ErrorCall callback) async {
    Environment.debug("parsing a generic network error");
    // Unauthorized problems could be due to our device ID being yanked out from under us on the back-end
    // Reregister and see if this solves the problem. We only try to re-register once (tries == 0): if that
    // does not fix the problem, something else is wrong.
    // If we may expect an unAuth reply, let it go.
    // 401 = Unauthorized, 403 = Forbidden
    if ((e.code == 403 || e.code == 401) && tries == 0 && !expectUnauthenticated) {
      Environment.debug("received an unauthenticated call and we did not expect that. Trying to reregister");
      try {
        var device = await registerDevice();
        await Environment.instance.set('deviceId', device.deviceId);
        Environment.instance.authToken = device.deviceId;
        Environment.debug("calling callback after failure to authenticate ${device.deviceId}");
        return await callback(tries + 1);
      } on Exception {
        Environment.debug('Caught exception while trying to reregister after unauth result');
        throw e;
      }
    }
    // in case of potential temporary errors, retry after a delay, to see if the
    // server comes back online (could be a deployment problem)
    // 500 = Internal Server Error, 503 = Service Unavailable, 504 = Gateway Timeout
    else if ((e.code == 500 || e.code == 503 || e.code == 504) && tries < 5) {
      Environment.debug("received an internal server error, retrying after some time");
      return await Future.delayed(const Duration(milliseconds: 500), () async {
        Environment.debug("retrying callback after server failure");
        try {
          return await callback(tries + 1);
        } on Exception {
          Environment.error('Caught exception while trying rerun after server failure');
        }
        return {};
      });
    } else {
      Environment.error("could not manage network error ${e.code}");
    }
    return {};
  }

  Future<String> _parseResponse(http.Response response) async {
    try {
      if (response.statusCode == 200) {
        Environment.debug("response ${response.body}");
        return response.body;
      } else {
        Environment.debug("statuscode is ${response.statusCode}");
        throw NetworkError(response.statusCode, "Content error");
      }
    } on NetworkError {
      // non 200 statuscodes
      rethrow;
    } on TimeoutException {
      Environment.debug("encountered timeout");
      throw NetworkError(0, "Timeout error");
    } on HttpException {
      Environment.debug("encountered HTTP error");
      throw NetworkError(1, "HTTP error");
    } on Exception {
      Environment.debug("encountered generic error");
      throw NetworkError(2, "Other error");
    }
  }
}

class NetworkError implements Exception {
  String message;
  int code;

  NetworkError(this.code, this.message);
}

class ParameterError implements Exception {
  String message;

  ParameterError(this.message);
}