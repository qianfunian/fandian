USE [wxfd_allcity];

--Coordinate
PRINT 'Table [dbo].[Coordinate] ...';
INSERT INTO [dbo].[Coordinate] (
	[CoordGuid],
	[CityId],
	[RegionGuid],
	[CoordName],
	[CoordType],
	[Longitude],
	[Latitude],
	[CoordValue],
	[InputCode],
	[Disabled],
	[AddTime],
	[AddUser],
	[Audited],
	[AuditTime],
	[AuditUser]
)
SELECT [CoordGuid],
		'cz',
		[RegionGuid],
		[CoordName],
		[CoordType],
		[Longitude],
		[Latitude],
		[CoordValue],
		[InputCode],
		[Disabled],
		[AddTime],
		[AddUser],
		1,
		GETDATE(),
		'System'
		FROM [ChangZhou_Fandian].[dbo].[Coordinate]
;

PRINT ''
PRINT ''
PRINT '=========================================================='
PRINT 'DONE'
PRINT '=========================================================='
PRINT ''
PRINT ''

