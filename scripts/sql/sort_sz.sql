use [SuZhou_Fandian];


--CHANGE SORT
ALTER TABLE [SuZhou_Fandian].[dbo].[CancelReason]
	ALTER COLUMN [Reason] NVARCHAR(80) COLLATE Chinese_PRC_CI_AS;
	
DROP INDEX IX_Category_CtgName ON [SuZhou_Fandian].[dbo].[Category];
ALTER TABLE [SuZhou_Fandian].[dbo].[Category]
	ALTER COLUMN [CtgName] NVARCHAR(60) COLLATE Chinese_PRC_CI_AS;
	
	
DROP INDEX IX_CategoryGroup_CtgGroupName ON [SuZhou_Fandian].[dbo].[CategoryGroup];
ALTER TABLE [SuZhou_Fandian].[dbo].[CategoryGroup]
	ALTER COLUMN [CtgGroupName] NVARCHAR(200) COLLATE Chinese_PRC_CI_AS;
	
	
DROP INDEX IX_CategoryStandard_TargetObject_CtgStdName ON [SuZhou_Fandian].[dbo].[CategoryStandard];
ALTER TABLE [SuZhou_Fandian].[dbo].[CategoryStandard]
	ALTER COLUMN [CtgStdName] NVARCHAR(60) COLLATE Chinese_PRC_CI_AS;
	
DROP INDEX IX_OrderPayment_1 ON [SuZhou_Fandian].[dbo].[OrderPayment];
ALTER TABLE [SuZhou_Fandian].[dbo].[OrderPayment]
	ALTER COLUMN [Hash] NCHAR(40) COLLATE Chinese_PRC_CI_AS;

	
DROP INDEX IX_ItemUnit_UnitName ON [SuZhou_Fandian].[dbo].[ItemUnit];
ALTER TABLE [SuZhou_Fandian].[dbo].[ItemUnit]
	ALTER COLUMN [UnitName] NVARCHAR(10) COLLATE Chinese_PRC_CI_AS;
	
	
ALTER TABLE [SuZhou_Fandian].[dbo].[SalesAttribute]
	ALTER COLUMN [AttributeName] NVARCHAR(60) COLLATE Chinese_PRC_CI_AS;
	
	
DROP INDEX IX_User_UserId ON [SuZhou_Fandian].[dbo].[User];
ALTER TABLE [SuZhou_Fandian].[dbo].[User]
	ALTER COLUMN [UserId] NVARCHAR(20) COLLATE Chinese_PRC_CI_AS;


DROP INDEX IX_CustomerPhone_PhoneGuid_PhoneNumber ON [SuZhou_Fandian].[dbo].[CustomerPhone];
DROP INDEX IX_CustomerPhone_PhoneNumber ON [SuZhou_Fandian].[dbo].[CustomerPhone];
DROP INDEX IX_CustomerPhone_PhoneNumber_PhoneType ON [SuZhou_Fandian].[dbo].[CustomerPhone];
ALTER TABLE [SuZhou_Fandian].[dbo].[CustomerPhone]
	ALTER COLUMN [PhoneNumber] NVARCHAR(20) COLLATE Chinese_PRC_CI_AS;
	
	
ALTER TABLE [SuZhou_Fandian].[dbo].[Region]
	ALTER COLUMN [RegionName] NVARCHAR(40) COLLATE Chinese_PRC_CI_AS;