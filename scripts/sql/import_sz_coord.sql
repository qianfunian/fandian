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
		'sz',
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
		FROM [SuZhou_Fandian].[dbo].[Coordinate]
;
/* 城区 */
UPDATE [dbo].[Coordinate] 
	SET [RegionGuid]='A2F85398-695E-42CA-91B3-DD1FAAB6E6C7'
	WHERE [RegionGuid]='452EC21F-3194-4A59-B254-599715658799';
/* 园区 */
UPDATE [dbo].[Coordinate]
	SET [RegionGuid]='A5688FC6-72BB-43E9-8F9F-5B0C5C70AF53'
	WHERE [RegionGuid]='F0A16031-CC3A-4C48-A155-2AB7E51FCCC3';

PRINT ''
PRINT ''
PRINT '=========================================================='
PRINT 'DONE'
PRINT '=========================================================='
PRINT ''
PRINT ''

