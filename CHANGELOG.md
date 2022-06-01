# Changelog

### main branch

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