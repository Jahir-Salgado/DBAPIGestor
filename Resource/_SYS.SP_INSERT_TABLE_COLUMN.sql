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
CREATE PROCEDURE _SYS.SP_INSERT_TABLE_COLUMN
	-- Add the parameters for the stored procedure here
	@TABLE_ID INT,
	@COLUMN_NAME VARCHAR(255),
	@DATA_TYPE_ID INT,
	@INDEX VARCHAR(255),
	@LEN1 INT = NULL,
	@LEN2 INT = NULL,
	@IS_IDENTITY VARCHAR(5) = 'N',
	@SEED INT = 1,
	@INCREMENT INT = 1,
	@IS_NULLABLE VARCHAR(5) = 'Y',
	@DEFAULT_VALUE VARCHAR(255) = NULL,
	@FK_TABLE_ID INT = NULL,
	@FK_COLUMN_ID INT = NULL
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;


	DECLARE @columnObjId INT = 1009, @tableObjId INT = 1004
	DECLARE @tableName VARCHAR(255), @SchemaName VARCHAR(255), @dataTypeName VARCHAR(255), @dataTypeParams INT


	--------------- VALIDAR SI EXISTE TABLA PADRE ---------------

	IF NOT EXISTS (SELECT * FROM _SYS.TBL_SERVERS_OBJECTS WHERE OBJ_ID = @TABLE_ID AND OBJ_TYPE_ID = @tableObjId)
	BEGIN 
		SELECT 0 AS SUCCESS, CONCAT('El objeto [',@TABLE_ID,'] de tipo [Table] no existe') AS [MESSAGE] 
		RETURN;
	END

	SELECT @tableName = OBJ_NAME, @SchemaName = OBJ_SCHEMA_NAME
	FROM _SYS.TBL_SERVERS_OBJECTS WHERE OBJ_ID = @TABLE_ID AND OBJ_TYPE_ID = @tableObjId


	--------------- VALIDAR SI EXISTE LA COLUMNA ---------------


	IF EXISTS (SELECT * FROM _SYS.TBL_SERVERS_OBJECTS WHERE OBJ_PARENT_OF = @TABLE_ID AND OBJ_NAME = @COLUMN_NAME AND OBJ_TYPE_ID = @columnObjId)
	BEGIN 
		SELECT 0 AS SUCCESS, CONCAT('La columna [',@COLUMN_NAME,'] ya existe dentro de la tabla [',@SchemaName,'].[',@tableName,']') AS [MESSAGE] 
		RETURN;
	END


	--------------- VALIDAR QUE EL INDICE DE LA COLUMNA SEA VALIDO ---------------


	IF @INDEX IS NOT NULL AND @INDEX NOT IN ('PRIMARY KEY','UNIQUE','FOREIGN KEY')
	BEGIN
		SELECT 0 AS SUCCESS, CONCAT('El indice especificado en [',@COLUMN_NAME,'] no es valido') AS [MESSAGE]
		RETURN;
	END


	--------------- VALIDAR SI EL TIPO DE DATO ES VALIDO  ---------------


	IF NOT EXISTS (SELECT * FROM _SYS.TBL_DATA_TYPES WHERE DTYPE_ID = @DATA_TYPE_ID)
	BEGIN 
		SELECT 0 AS SUCCESS, CONCAT('El tipo de dato [',@DATA_TYPE_ID,'] en la columna [',@COLUMN_NAME,'] no es valido') AS [MESSAGE] 
		RETURN;
	END

	SELECT @dataTypeName = DTYPE_NAME, @dataTypeParams = DPARAMS_NUMBER FROM _SYS.TBL_DATA_TYPES WHERE DTYPE_ID = @DATA_TYPE_ID

	IF (@dataTypeParams = 1 AND @LEN1 IS NULL) OR (@dataTypeParams = 2 AND (@LEN1 IS NULL OR @LEN2 IS NULL))
	BEGIN
		SELECT 0 AS SUCCESS, CONCAT('El tipo de dato [',@dataTypeName,'] en la columna [',@COLUMN_NAME,'] esperaba ',@dataTypeParams,' parametro') AS [MESSAGE]
		RETURN;
	END


	IF @INDEX IN ('FOREIGN KEY') AND NOT EXISTS (
		SELECT * FROM _SYS.TBL_SERVERS_OBJECTS WHERE OBJ_ID = @FK_COLUMN_ID AND OBJ_TYPE_ID = @columnObjId AND OBJ_PARENT_OF = @FK_TABLE_ID
	)
	BEGIN
		SELECT 0 AS SUCCESS, 'Llave foranea no es valida. Relacion entre tabla y columna no concuerdan o no existen' AS [MESSAGE]
		RETURN;
	END


    SELECT @tableName, @column_name, @dataTypeName


END
GO
