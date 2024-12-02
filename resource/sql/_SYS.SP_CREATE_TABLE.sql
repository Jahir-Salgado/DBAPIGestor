-- ================================================
-- Template generated from Template Explorer using:
-- Create Procedure (New Menu).SQL
--
-- Use the Specify Values for Template Parameters 
-- command (Ctrl-Shift-M) to fill in the parameter 
-- values below.
--
-- This block of comments will not be included in
-- the definition of the procedure.
-- ================================================
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
-- =============================================
-- Author:		<Author,,Name>
-- Create date: <Create Date,,>
-- Description:	<Description,,>
-- =============================================
CREATE PROCEDURE _SYS.SP_CREATE_TABLE
	@OBJ_ID INT
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

	------------------------------ DECLARACION DE PARAMETROS ------------------------------


	DECLARE @OBJ_COLUMN_ID INT = 1009;

	DECLARE @TableName NVARCHAR(255);
	DECLARE @SchemaName NVARCHAR(255);
	DECLARE @ColumnDefinition NVARCHAR(MAX) = '';
	DECLARE @CreateTableScript NVARCHAR(MAX);
	DECLARE @IndexDefinition NVARCHAR(MAX) = '';

	------------------------------ BUSCAR DATOS DE TABLA (NOMRBE, ESQUEMA, ETC.) ------------------------------

	/*
		esto lo que permite es que vez de lanzar el query el valor se almacene dentro de la variable y continue con el codigo
	*/
	SELECT 
		@TableName = OBJ_NAME, -- en este caso el valor de OBJ_NAME se almacena dentro de la variable @TableName
		@SchemaName = OBJ_SCHEMA_NAME
	FROM _SYS.TBL_SERVERS_OBJECTS
	WHERE OBJ_ID = @OBJ_ID;


	IF @TableName IS NULL
	BEGIN
		SELECT 0 AS SUCCESS, CONCAT('No se encontro el objeto [',@OBJ_ID,'] de tipo [Table]') AS [MESSAGE] 
		RETURN;
	END


	------------------------------ ITERACION DE COLUMNAS DE TABLA ------------------------------
	/* 
		la parte @ColumnDefinition = CONCAT(@ColumnDefinition... permite sobre escribir en la misma variable 
		al mismo tiempo que itera y concatena las filas del query
	*/

	SELECT 
		@ColumnDefinition = CONCAT(@ColumnDefinition,
			'[' + OBJ_NAME + '] ',
			(CASE
				WHEN T1.DPARAMS_NUMBER = 1 AND T0.OBJ_LEN_1 IS NOT NULL THEN CONCAT(T1.DTYPE_NAME,'(',T0.OBJ_LEN_1,')')
				WHEN T1.DPARAMS_NUMBER = 2 AND T0.OBJ_LEN_1 IS NOT NULL AND T0.OBJ_LEN_2 IS NOT NULL
				THEN CONCAT(T1.DTYPE_NAME,'(',T0.OBJ_LEN_1,',',T0.OBJ_LEN_2,')')
				ELSE T1.DTYPE_NAME
			END),
			(CASE WHEN OBJ_IS_NULLABLE = 'N' THEN ' NOT NULL' ELSE ' NULL' END),
			(CASE WHEN OBJ_HAS_IDENTITY = 'Y' THEN CONCAT(' IDENTITY(',OBJ_IDENTITY_SEED,',',OBJ_IDENTITY_INCREMENT,')') ELSE '' END),
			(CASE WHEN OBJ_DEFAULT_VALUE IS NOT NULL THEN ' DEFAULT ''' + OBJ_DEFAULT_VALUE + '''' ELSE '' END),
			',',
			CHAR(13))
	FROM _SYS.TBL_SERVERS_OBJECTS AS T0
	LEFT JOIN _SYS.TBL_DATA_TYPES AS T1 ON T1.DTYPE_ID = T0.OBJ_DATATYPE_ID
	WHERE OBJ_PARENT_OF = @OBJ_ID AND T0.OBJ_TYPE_ID = @OBJ_COLUMN_ID;



	------------------------------ ITERACION DE INDICES DE LA TABLA ------------------------------
	/* 
		la parte @IndexDefinition = CONCAT(@IndexDefinition... permite sobre escribir en la misma variable 
		al mismo tiempo que itera y concatena las columnas con indices, solo se llaman las columnas que tengan un indice
	*/

	SELECT 
		@IndexDefinition = CONCAT(
			@IndexDefinition,
			--CONCAT(
			(CASE 
				WHEN T0.OBJ_INDEX = 'PRIMARY KEY' THEN CONCAT('PRIMARY KEY (',T0.OBJ_NAME,')')
				WHEN T0.OBJ_INDEX = 'UNIQUE' THEN CONCAT('UNIQUE (',T0.OBJ_NAME,')')
				WHEN T0.OBJ_INDEX = 'FOREIGN KEY' AND T0.OBJ_FK_TABLE_ID IS NOT NULL AND T0.OBJ_FK_COLUMN_ID IS NOT NULL THEN 
					CONCAT('FOREIGN KEY (',T0.OBJ_NAME,') REFERENCES [',FK_TBL.OBJ_SCHEMA_NAME,'].[',FK_TBL.OBJ_NAME,']([',FK_COL.OBJ_NAME,'])')
				ELSE ''
			END),
			',',
			CHAR(13)
		)
	FROM _SYS.TBL_SERVERS_OBJECTS AS T0
	LEFT JOIN _SYS.TBL_SERVERS_OBJECTS AS FK_TBL ON FK_TBL.OBJ_ID = T0.OBJ_FK_TABLE_ID
	LEFT JOIN _SYS.TBL_SERVERS_OBJECTS AS FK_COL ON FK_COL.OBJ_ID = T0.OBJ_FK_COLUMN_ID
	WHERE T0.OBJ_PARENT_OF = @OBJ_ID AND T0.OBJ_TYPE_ID = @OBJ_COLUMN_ID AND T0.OBJ_INDEX IS NOT NULL;


	-- esta parte es para eliminar la ultima coma al final de los string
	SET @ColumnDefinition = LEFT(@ColumnDefinition, LEN(@ColumnDefinition) - 2);
	SET @IndexDefinition = LEFT(@IndexDefinition, LEN(@IndexDefinition) - 2);



	------------------------------ CREACION DE TABLA SQL ------------------------------
	/* 
		En esta parte se concatenan las columnas y los indices en un solo string con la sentencia CREATE TABLE...
		luego se ejecuta con EXECUTE, ejecutando el string como que fuera una consulta SQL
	*/

	SET @CreateTableScript = CONCAT(
		'CREATE TABLE [',@SchemaName,'].[',@TableName,'] (',CHAR(13),
		@ColumnDefinition,
		(CASE WHEN @IndexDefinition IS NOT NULL THEN CONCAT(',',CHAR(13),CHAR(13),@IndexDefinition) ELSE '' END),
		CHAR(13),');'
	);


	BEGIN TRY
		PRINT @CreateTableScript;
		EXECUTE (@CreateTableScript)

		SELECT 1 AS SUCCESS, 'Tabla creada con exito' AS [MESSAGE] 
	END TRY
	BEGIN CATCH 
		SELECT 0 AS SUCCESS, ERROR_MESSAGE() AS [MESSAGE] 
	END CATCH


END
GO
