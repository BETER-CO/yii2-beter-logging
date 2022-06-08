# Changelog

### 1.2.2

- Before this release empty values of env settings (like `NO_COLOR`) and values with any string that differ from
`True` or `1` were interpreted as `false` (switched off). From now they will be interpreted as `null`
(invalid setting) in such cases.

### 1.2.1

- Support of exec time tracking was added to the Stats class.
- standard_stream handler supports Stats now.
- ProcessHandlerStatsInterface was renamed to HandlerStatsInterface.

### 1.2.0

- Support of contexts for exceptions and messages.

### 1.1.1

- Support of the correlationId (via processor). Generates hex string for CLI, tries to use `X-Reuqest-Id` header
if set and then switches to random generated value.

### 1.1.0

- MonologComponent must not throw Exceptions if YII_DEBUG is enabled
[[#1](https://github.com/BETER-CO/yii2-beter-logging/issues/1)].
- Redundant new line before `log.trace` output in the `console` formatter.
- Improvement in readability of th `console` formatter output.
- `context` field removed from output for logstash formatter. 
- `ProxyLogTarget` supports delayed errors with a context.
- Docker and docker-compose for development and testing purposes.
- A bunch of helper methods for env settings processing: NO_COLOR, LOGGER_MACHINE_READABLE, LOGGER_ENABLE_LOGSTASH.
- `logstash` service was added to docker-compose.

### 1.0.0

- Initial version.