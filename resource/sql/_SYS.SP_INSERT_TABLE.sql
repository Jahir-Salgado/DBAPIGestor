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
ALTER PROCEDURE _SYS.SP_INSERT_TABLE
	@DB INT,
	@SCHEMAID INT,
	@TABLENAME VARCHAR(255)
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;


	---------------------------------------------------------------------


	DECLARE @schemaObjId INT = 1003, @databaseObjId INT = 1002, @tableObjId INT = 1004
	DECLARE @schemaName VARCHAR(255)


	----------------------- VALIDAR SI EL ESQUEMA EXISTE -----------------------


	IF NOT EXISTS(SELECT * FROM _SYS.TBL_SERVERS_OBJECTS WHERE OBJ_ID = @db AND OBJ_TYPE_ID = @databaseObjId)
	BEGIN
		SELECT 0 AS SUCCESS, CONCAT('No se encontro el objeto [',@db,'] de tipo [Base de datos]') AS [MESSAGE], NULL AS [DATA]
		RETURN;
	END


	----------------------- VALIDAR SI EL ESQUEMA EXISTE -----------------------


	SELECT @schemaName = OBJ_NAME FROM _SYS.TBL_SERVERS_OBJECTS
	WHERE OBJ_ID = @schemaId AND OBJ_TYPE_ID = @schemaObjId

	IF @schemaName IS NULL
	BEGIN
		SELECT 0 AS SUCCESS, CONCAT('No se encontro el objeto [',@schemaId,'] de tipo [Schema]') AS [MESSAGE], NULL AS [DATA]
		RETURN;
	END


	----------------------- VALIDAR SI LA TABLA YA EXISTE EN DB -----------------------

	IF EXISTS(SELECT * FROM _SYS.TBL_SERVERS_OBJECTS WHERE OBJ_NAME = @tableName AND OBJ_SCHEMA_ID = @schemaId AND OBJ_TYPE_ID = @tableObjId)
	BEGIN
		SELECT 0 AS SUCCESS, CONCAT('El objeto [',@schemaName,'].[',@tableName,'] ya existe en base de datos' ) AS [MESSAGE], NULL AS [DATA]
		RETURN;
	END

	----------------------- INSERTAR TABLA -----------------------


	BEGIN TRY
		
		INSERT INTO _SYS.TBL_SERVERS_OBJECTS 
			(OBJ_TYPE_ID, OBJ_NAME, OBJ_SCHEMA_ID, OBJ_SCHEMA_NAME, OBJ_PARENT_OF)
		VALUES (@tableObjId, @tableName, @schemaId, @schemaName, @db)

		SELECT 1 AS SUCCESS, 'Tabla insertada con exito' AS [MESSAGE], @@IDENTITY AS [DATA] 

	END TRY
	BEGIN CATCH 
		SELECT 0 AS SUCCESS, ERROR_MESSAGE() AS [MESSAGE], NULL AS [DATA]
	END CATCH

END
GO
