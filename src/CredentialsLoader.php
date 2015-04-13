<?php
/*
 * Copyright 2015 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Auth;

use GuzzleHttp\Stream\Stream;

/**
 * CredentialsLoader contains the behaviour used to locate and find default
 * credentials files on the file system.
 */
class CredentialsLoader
{
  const ENV_VAR = 'GOOGLE_APPLICATION_CREDENTIALS';
  const WELL_KNOWN_PATH = 'gcloud/application_default_credentials.json';

  private static function unableToReadEnv($cause)
  {
    $msg = 'Unable to read the credential file specified by ';
    $msg .= ' GOOGLE_APPLICATION_CREDENTIALS: ';
    $msg .= $cause;
    return $msg;
  }

  private static function isOnWindows()
  {
    return strtoupper(substr(php_uname('s'), 0, 3)) === 'WIN';
  }

  /**
   * Create a credentials instance from the path specified in the environment.
   *
   * Creates a credentials instance from the path specified in the environment
   * variable GOOGLE_APPLICATION_CREDENTIALS. Return null if
   * GOOGLE_APPLICATION_CREDENTIALS is not specified.
   *
   * @param string|array scope the scope of the access request, expressed
   *   either as an Array or as a space-delimited String.
   *
   * @return a Credentials instance | null
   */
  public static function fromEnv($scope = null)
  {
    $path = getenv(self::ENV_VAR);
    if (empty($path)) {
      return null;
    }
    if (!file_exists($path)) {
      $cause = "file " . $path . " does not exist";
      throw new \DomainException(self::unableToReadEnv($cause));
    }
    $keyStream = Stream::factory(file_get_contents($path));
    return new static($scope, $keyStream);
  }

  /**
   * Create a credentials instance from a well known path.
   *
   * The well known path is OS dependent:
   * - windows: %APPDATA%/gcloud/application_default_credentials.json
   * - others: $HOME/.config/gcloud/application_default_credentials.json
   *
   * If the file does not exists, this returns null.
   *
   * @param string|array scope the scope of the access request, expressed
   *   either as an Array or as a space-delimited String.
   *
   * @return a Credentials instance | null
   */
  public static function fromWellKnownFile($scope = null)
  {
    $rootEnv = self::isOnWindows() ? 'APPDATA' : 'HOME';
    $root = getenv($rootEnv);
    $path = join(DIRECTORY_SEPARATOR, [$root, self::WELL_KNOWN_PATH]);
    if (!file_exists($path)) {
      return null;
    }
    $keyStream = Stream::factory(file_get_contents($path));
    return new static($scope, $keyStream);
  }

}
