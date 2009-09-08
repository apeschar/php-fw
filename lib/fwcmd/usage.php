<?=$this->_command?> <command> [<argument> ...]

help:
  show this message.

project <directory>:
  create a project in the specified directory.

propel:build:                   build ORM classes.
propel:insert-sql:              insert SQL built with propel:build.
propel:fixtures:                load fixtures.
propel:build-insert:            execute build and insert-sql.
propel:build-insert-data:       execute build, insert-sql and fixtures.

