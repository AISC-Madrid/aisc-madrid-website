# Un guest es una persona y su perfil se conserva

Los guests guardan datos de contacto (email, teléfono, LinkedIn) en la propia tabla `guests` en lugar de en una tabla de ponentes separada de las cuentas de acceso. Por eso un guest representa siempre a **una sola persona**: las cuentas compartidas (p. ej. dos ponentes de una misma charla) se separan en un guest por persona. Cuando un guest deja de tener eventos se **desactiva** en vez de borrarse, para conservar su contacto y poder reactivarlo si vuelve.

## Considered Options

- Tabla `speakers` independiente de `guests` (logins): más flexible para cuentas compartidas, pero duplica el concepto de persona y hoy no hace falta.
