# Sistema Web de Admisión Universitaria FICCT

Proyecto desarrollado para la materia Sistemas de Información I.

## Descripción

El sistema permite administrar el proceso de admisión universitaria de la FICCT, gestionando postulantes, carreras, aulas, materias, docentes y grupos. También calcula la cantidad de grupos habilitados considerando un máximo de 70 estudiantes por grupo.

## Tecnologías utilizadas

- PHP
- PostgreSQL
- HTML5
- CSS3
- JavaScript
- Laragon
- Apache
- pgAdmin 4
- Visual Studio Code

## Módulos implementados

- Inicio de sesión
- Cierre de sesión
- Panel principal
- Gestión de postulantes
- Gestión de carreras
- Gestión de aulas
- Gestión de materias
- Gestión de docentes
- Gestión de grupos
- Cálculo de grupos habilitados

## Base de datos

La base de datos se encuentra en la carpeta:

database/

Archivos principales:

- schema.sql

## Instalación

1. Copiar el proyecto dentro de la carpeta www de Laragon.
2. Crear una base de datos en PostgreSQL llamada:

bd_fitcct_postulantes

3. Ejecutar el archivo:

database/schema.sql

4. Ejecutar el archivo de datos de prueba:

database/datos_prueba.sql

5. Configurar la conexión en:

config/database.php

6. Iniciar Laragon y abrir en el navegador:

http://localhost/fitcct_postulantes/

## Usuario de prueba

Usuario: admin  
Contraseña: 123456
