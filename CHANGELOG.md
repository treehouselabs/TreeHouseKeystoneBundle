CHANGELOG
=========

## v2.0.0

### Changes
* Added Symfony3 support
* Implemented EntryPoint for valid status codes

### Breaking changes
* Now requiring PHP 5.6
* Only Symfony 2.6 and up is now supported
* Made most security-related services private
* `TokenManager::validate()` has been removed


## v1.0.3

### Changes
* Different response code when token is expired, per the Keystone spec
* Renamed `TokenManager::validate` to `TokenManager::isExpired`.

### Deprecations
* `TokenManager::validate()` is now deprecated


## v1.0.2

### Changes
* Hotfix for grants


## v1.0.1

### Changes
* Added findServiceByName to ServiceManager
* Added tests
* Fixed response status code for access denied exception


## 1.0.0

Initial release
