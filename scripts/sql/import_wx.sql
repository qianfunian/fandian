USE [wxfd_allcity];

DECLARE @CityId NVARCHAR(50)='wx';
DECLARE @CityGuid NVARCHAR(50)='CB081590-9C73-4940-A0E0-87F83263BAC0';
DECLARE @AreaGuid NVARCHAR(50)='01C9A0B0-6D82-4633-942F-3B7E5ADA9B85';
DECLARE @InfoChanged BIT=1;
DECLARE @Paid BIT=1;
DECLARE @StaffType NVARCHAR(50)='全职';
DECLARE @PartitionColumn NVARCHAR(50)='CityId';
DECLARE @Audited BIT=1;
DECLARE @CustChanged BIT=0;
DECLARE @ItemChanged BIT=0;

--NewWebSales
DELETE FROM [dbo].[NewWebSales];

--Rights
PRINT 'Table [dbo].[Rights] ...';
DELETE FROM [dbo].[Rights];
INSERT INTO [dbo].[Rights] (
	[RightsGuid],
	[SortIndex],
	[Category],
	[ObjectId],
	[ObjectType],
	[RightsName],
	[RightsType]
)
SELECT [RightsGuid],
		[SortIndex],
		[Category],
		[ObjectId],
		[ObjectType],
		[RightsName],
		[RightsType]
		FROM [Wuxi_Fandian].[dbo].[Rights]
;

--AccessRights
PRINT 'Table [dbo].[AccessRights] ...';
DELETE FROM [dbo].[AccessRights];
INSERT INTO [dbo].[AccessRights] (
	[Language],
	[Name],
	[Value],
	[RightsType]
)
SELECT [Language],
		[Name],
		[Value],
		[RightsType]
		FROM [Wuxi_Fandian].[dbo].[AccessRights]
;
--BlackList
PRINT 'Table [dbo].[BlackList] ...';
DELETE FROM [dbo].[BlackList];
INSERT INTO [dbo].[BlackList] (
	[BlackListGuid],
	[CityId],
	[CustGuid],
	[AddReason],
	[AddTime],
	[AddUser],
	[CancelReason],
	[CancelTime],
	[CancelUser],
	[Remark]
)
SELECT [BlackListGuid],
		@CityId,
		[CustGuid],
		[AddReason],
		[AddTime],
		[AddUser],
		[CancelReason],
		[CancelTime],
		[CancelUser],
		[Remark]
		FROM [Wuxi_Fandian].[dbo].[BlackList]
;
--CallUser
PRINT 'Table [dbo].[CallUser] ...';
DELETE FROM [dbo].[CallUser];
INSERT INTO [dbo].[CallUser] (
	[UserId],
	[account],
	[password],
	[extno]
)
SELECT [UserId],
		[account],
		[password],
		[extno]
		FROM [Wuxi_Fandian].[dbo].[CallUser]
;
--CancelReason
PRINT 'Table [dbo].[CancelReason] ...';
DELETE FROM [dbo].[CancelReason];
INSERT INTO [dbo].[CancelReason] (
	[CancelGuid],
	[Initiator],
	[Reason],
	[Disabled],
	[AddUser],
	[AddTime]
)
SELECT [CancelGuid],
		[Initiator],
		[Reason],
		[Disabled],
		[AddUser],
		[AddTime]
		FROM [Wuxi_Fandian].[dbo].[CancelReason]
;
--Category
PRINT 'Table [dbo].[Category] ...';
DELETE FROM [dbo].[Category];
INSERT INTO [dbo].[Category] (
	[CtgGuid],
	[CtgStdGuid],
	[CtgName],
	[Description],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [CtgGuid],
		[CtgStdGuid],
		[CtgName],
		[Description],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[Category]
;
--CategoryGroup
PRINT 'Table [dbo].[CategoryGroup] ...';
DELETE FROM [dbo].[CategoryGroup];
INSERT INTO [dbo].[CategoryGroup] (
	[CtgGroupGuid],
	[CtgGroupName],
	[TargetObject],
	[AppliedObject],
	[Description],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [CtgGroupGuid],
		[CtgGroupName],
		[TargetObject],
		[AppliedObject],
		[Description],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[CategoryGroup]
;
--CategoryGroupMember
PRINT 'Table [dbo].[CategoryGroupMember] ...'
DELETE FROM [dbo].[CategoryGroupMember];
INSERT INTO [dbo].[CategoryGroupMember] (
	[CGMGuid],
	[CtgGroupGuid],
	[CtgGuid]
)
SELECT [CGMGuid],
		[CtgGroupGuid],
		[CtgGuid]
		FROM [Wuxi_Fandian].[dbo].[CategoryGroupMember]
;
--CategoryStandard
PRINT 'Table [dbo].[CategoryStandard] ...';
DELETE FROM [dbo].[CategoryStandard];
INSERT INTO [dbo].[CategoryStandard] (
	[CtgStdGuid],
	[CtgStdName],
	[TargetObject],
	[IsPrimary],
	[IsRequired],
	[Multichoice],
	[Description],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [CtgStdGuid],
		[CtgStdName],
		[TargetObject],
		[IsPrimary],
		[IsRequired],
		[Multichoice],
		[Description],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[CategoryStandard]
;
--Coordinate
PRINT 'Table [dbo].[Coordinate] ...';
DELETE FROM [dbo].[Coordinate];
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
		@CityId,
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
		@Audited,
		GETDATE(),
		'system'
		FROM [Wuxi_Fandian].[dbo].[Coordinate]
;
--CoordniateUpload
PRINT 'Table [dbo].[CoordinateUpload] ...';
DELETE FROM [dbo].[CoordinateUpload];
INSERT INTO [dbo].[CoordinateUpload] (
	[CuGuid],
	[AddTime],
	[CoordName],
	[Longitude],
	[Latitude],
	[CoordValue],
	[DlvManGuid]
)
SELECT [CuGuid],
		[AddTime],
		[CoordName],
		[Longitude],
		[Latitude],
		[CoordValue],
		[DlvManGuid]
		FROM [Wuxi_Fandian].[dbo].[CoordinateUpload]
;
--Customer
PRINT 'Table [dbo].[Customer] ...';
DELETE FROM [dbo].[Customer];
INSERT INTO [dbo].[Customer] (
	[CustGuid],
	[CityId],
	[CityGuid],
	[AreaGuid],
	[CustId],
	[CustName],
	[CtgGroupGuid],
	[Company],
	[Mail],
	[Remark],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [CustGuid],
		@CityId,
		@CityGuid,
		@AreaGuid,
		[CustId],
		[CustName],
		[CtgGroupGuid],
		[Company],
		[Mail],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[Customer]
;
--CustomerAddress
PRINT 'Table [dbo].[CustomerAddress] ...';
DELETE FROM [dbo].[CustomerAddress];
INSERT INTO [dbo].[CustomerAddress] (
	[AddressGuid],
	[CityId],
	[CustGuid],
	[CustAddress],
	[CoordGuid],
	[CoordName],
	[CoordValue],
	[Remark],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [AddressGuid],
		@CityId,
		[CustGuid],
		[CustAddress],
		[CoordGuid],
		[CoordName],
		[CoordValue],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[CustomerAddress]
;
--CustomerExpedite
PRINT 'Table [dbo].[CustomerExpedite] ...';
DELETE FROM [dbo].[CustomerExpedite];
INSERT INTO [dbo].[CustomerExpedite] (
	[XpdGuid],
	[CityId],
	[SalesGuid],
	[ExpediteTime],
	[CustMessage],
	[Remark],
	[AddUser],
	[AddTime]
)
SELECT [XpdGuid],
		@CityId,
		[SalesGuid],
		[ExpediteTime],
		[CustMessage],
		[Remark],
		[AddUser],
		[AddTime]
		FROM [Wuxi_Fandian].[dbo].[CustomerExpedite]
;
--CustomerPhone
PRINT 'Table [dbo].[CustomerPhone] ...';
DELETE FROM [dbo].[CustomerPhone];
INSERT INTO [dbo].[CustomerPhone] (
	[PhoneGuid],
	[CityId],
	[CustGuid],
	[PhoneNumber],
	[PhoneType],
	[Remark],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [PhoneGuid],
		@CityId,
		[CustGuid],
		[PhoneNumber],
		[PhoneType],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[CustomerPhone]
;
--CustOrder
PRINT 'Table [dbo].[CustOrder] ...';
DELETE FROM [dbo].[CustOrder];
INSERT INTO [dbo].[CustOrder] (
	[Phone],
	[OrderTime],
	[CustGuid]
)
SELECT [Phone],
		[OrderTime],
		[CustGuid]
		FROM [Wuxi_Fandian].[dbo].[CustOrder]
;
--D_Chat
PRINT 'Table [dbo].[D_Chat] ...';
DELETE FROM [dbo].[D_Chat];
INSERT INTO [dbo].[D_Chat] (
	[Sender],
	[Receiver],
	[SendTime],
	[Message],
	[CityGuid]
)
SELECT [sender],
		[receiver],
		[send_time],
		[content],
		@CityGuid
		FROM [Wuxi_Fandian].[dbo].[D_Chat]
;
--D_Checkout
PRINT 'Table [dbo].[D_Checkout] ...';
DELETE FROM [dbo].[D_Checkout];
INSERT INTO [dbo].[D_Checkout] (
	[OrderId],
	[DlvManId],
	[BaoXiao],
	[FaPiao],
	[Comment],
	[CityGuid]
)
SELECT [OrderId],
		[DlvManId],
		[BaoXiao],
		[FaPiao],
		[Comment],
		@CityGuid
		FROM [Wuxi_Fandian].[dbo].[D_Checkout]
;
--D_DeliveryOrder
PRINT 'Table [dbo].[D_DeliveryOrder] ...';
DELETE FROM [dbo].[D_DeliveryOrder];
INSERT INTO [dbo].[D_DeliveryOrder] (
	[OrderGuid],
	[DlvManGuid],
	[AcceptTime],
	[Disabled],
	[AddTime],
	[AddUser],
	[CityGuid]
)
SELECT [OrderGuid],
		[DlvManGuid],
		[AcceptTime],
		[Disabled],
		[AddTime],
		[AddUser],
		@CityGuid
		FROM [Wuxi_Fandian].[dbo].[D_DeliveryOrder]
;
--D_GpsOffset
PRINT 'Table [dbo].[D_GpsOffset] ...';
DELETE FROM [dbo].[D_GpsOffset];
INSERT INTO [dbo].[D_GpsOffset] (
	[lat_o],
	[lng_o],
	[lat_b],
	[lng_b]
)
SELECT [lat_o],
		[lng_o],
		[lat_b],
		[lng_b]
		FROM [Wuxi_Fandian].[dbo].[D_GpsOffset]
;
--D_HistoryGps
PRINT 'Table [dbo].[D_HistoryGps] ...';
DELETE FROM [dbo].[D_HistoryGps];
INSERT INTO [dbo].[D_HistoryGps] (
	[DlvManId],
	[HeartBeatTime],
	[Longitude],
	[Latitude]
)
SELECT [DlvManId],
		[HeartBeatTime],
		[Longitude],
		[Latitude]
		FROM [Wuxi_Fandian].[dbo].[D_HistoryGps]
;
--D_UploadPoint
PRINT 'Table [dbo].[D_UploadPoint] ...';
DELETE FROM [dbo].[D_UploadPoint];
INSERT INTO [dbo].[D_UploadPoint] (
	[CityID],
	[RegionGuid],
	[Name],
	[Longitude],
	[Latitude],
	[AddUser],
	[AddTime],
	[Disabled]
)
SELECT @CityId,
		[RegionGuid],
		[Name],
		[Longitude],
		[Latitude],
		[AddUser],
		[AddTime],
		[Disabled]
		FROM [Wuxi_Fandian].[dbo].[D_UploadPoint]
;
--D_VendorDelay
PRINT 'Table [dbo].[D_VendorDelay] ...';
DELETE FROM [dbo].[D_VendorDelay];
INSERT INTO [dbo].[D_VendorDelay] (
	[OrderGuid],
	[Delayed],
	[AddTime],
	[AddUser],
	[Remarks]
)
SELECT [OrderGuid],
		[Delayed],
		[AddTime],
		[AddUser],
		[Remarks]
		FROM [Wuxi_Fandian].[dbo].[D_VendorDelay]
;
--DailyDlvSchedule
PRINT 'Table [dbo].[DailyDlvSchedule] ...';
DELETE FROM [dbo].[DailyDlvSchedule];
INSERT INTO [dbo].[DailyDlvSchedule] (
	[STGuid],
	[DlvDate],
	[FtSums],
	[PtSums],
	[LdSums],
	[JbSums],
	[Comment],
	[AddTime],
	[AddUser]
)
SELECT [STGuid],
		[DlvDate],
		[FtSums],
		[PtSums],
		[LdSums],
		[JbSums],
		[Comment],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[DailyDlvSchedule]
;
--Deliveryman
PRINT 'Table [dbo].[Deliveryman] ...';
DELETE FROM [dbo].[Deliveryman];
INSERT INTO [dbo].[Deliveryman] (
	[DlvManGuid],
	[CityId],
	[DCGuid],
	[DlvManId],
	[DlvManName],
	[StaffType],
	[WorkPhone],
	[Password],
	[InputCode],
	[Disabled],
	[AddTime],
	[AddUser],
	[LastHeartBeat],
	[LastLongitude],
	[LastLatitude]
)
SELECT [DlvManGuid],
		@CityId,
		[DCGuid],
		[DlvManId],
		[DlvManName],
		@StaffType,
		[WorkPhone],
		[DlvManPassword],
		[InputCode],
		[Disabled],
		[AddTime],
		[AddUser],
		[LastHeartBeat],
		[LastLongitude],
		[LastLatitude]
		FROM [Wuxi_Fandian].[dbo].[Deliveryman]
;
--DeliverySchedule
PRINT 'Table [dbo].[DeliverySchedule] ...';
DELETE FROM [dbo].[DeliverySchedule];
INSERT INTO [dbo].[DeliverySchedule] (
	[ScheduleGuid],
	[CityId],
	[DCGuid],
	[Date],
	[DlvManGuid],
	[AttendType],
	[ShiftGuid],
	[Remark],
	[AddTime],
	[AddUser]
)
SELECT [ScheduleGuid],
		@CityId,
		[DCGuid],
		[Date],
		[DlvManGuid],
		[AttendType],
		[ShiftGuid],
		[Remark],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[DeliverySchedule]
;
--DeliveryScheduleTime
PRINT 'Table [dbo].[DeliveryScheduleTime] ...';
DELETE FROM [dbo].[DeliveryScheduleTime];
INSERT INTO [dbo].[DeliveryScheduleTime] (
	[SSTGuid],
	[CityId],
	[ScheduleGuid],
	[ShiftType],
	[StartTime],
	[EndTime],
	[Qty],
	[Remark],
	[AddTime],
	[AddUser]
)
SELECT [SSTGuid],
	@CityId,
	[ScheduleGuid],
	[ShiftType],
	[StartTime],
	[EndTime],
	[Qty],
	[Remark],
	[AddTime],
	[AddUser]
	FROM [Wuxi_Fandian].[dbo].[DeliveryScheduleTime]
;
--DeliveryShift
PRINT 'Table [dbo].[DeliveryShift] ...';
DELETE FROM [dbo].[DeliveryShift];
INSERT INTO [dbo].[DeliveryShift] (
	[ShiftGuid],
	[CityId],
	[DCGuid],
	[ShiftId],
	[ShiftName],
	[Remark],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [ShiftGuid],
	@CityId,
	[DCGuid],
	[ShiftId],
	[ShiftName],
	[Remark],
	[Disabled],
	[AddTime],
	[AddUser]
	FROM [Wuxi_Fandian].[dbo].[DeliveryShift]
;
--DeliveryShiftTime
PRINT 'Table [dbo].[DeliveryShiftTime] ...';
DELETE FROM [dbo].[DeliveryShiftTime];
INSERT INTO [dbo].[DeliveryShiftTime] (
	[STGuid],
	[CityId],
	[ShiftGuid],
	[StartTime],
	[EndTime],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [STGuid],
		@CityId,
		[ShiftGuid],
		[StartTime],
		[EndTime],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[DeliveryShiftTime]
;
--DistributionCenter
PRINT 'Table [dbo].[DistributionCenter] ...';
DELETE FROM [dbo].[DistributionCenter];
INSERT INTO [dbo].[DistributionCenter] (
	[DCGuid],
	[CityId],
	[CityGuid],
	[AreaGuid],
	[DCName],
	[Phone],
	[Longitude],
	[Latitude],
	[Coordinate],
	[Address],
	[Remark],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [DCGuid],
		@CityId,
		@CityGuid,
		@AreaGuid,
		[DCName],
		[Phone],
		[Longitude],
		[Latitude],
		[Coordinate],
		[Address],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[DistributionCenter]
;
--DivisionGroup
PRINT 'Table [dbo].[DivisionGroup] ...';
DELETE FROM [dbo].[DivisionGroup];
INSERT INTO [dbo].[DivisionGroup] (
	[DivGrpGuid],
	[CityId],
	[DGSGuid],
	[DivGrpName],
	[Description],
	[AddTime],
	[AddUser]
)
SELECT [DivGrpGuid],
		@CityId,
		[DGSGuid],
		[DivGrpName],
		[Description],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[DivisionGroup]
;
--DivisionGroupMember
PRINT 'Table [dbo].[DivisionGroupMember] ...';
DELETE FROM [dbo].[DivisionGroupMember];
INSERT INTO [dbo].[DivisionGroupMember] (
	[DGMGuid],
	[CityId],
	[DivGrpGuid],
	[MemberGuid],
	[MemberName],
	[AddTime],
	[AddUser]
)
SELECT [DGMGuid],
		@CityId,
		[DivGrpGuid],
		[MemberGuid],
		[MemberName],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[DivisionGroupMember]
;
--DivisionGroupScheme
PRINT 'Table [dbo].[DivisionGroupScheme] ...';
DELETE FROM [dbo].[DivisionGroupScheme];
INSERT INTO [dbo].[DivisionGroupScheme] (
	[DGSGuid],
	[CityId],
	[SchemeName],
	[TargetObject],
	[MemberUnique],
	[Description],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [DGSGuid],
		@CityId,
		[SchemeName],
		[TargetObject],
		[MemberUnique],
		[Description],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[DivisionGroupScheme]
;
--Enum
PRINT 'Table [dbo].[Enum] ...';
DELETE FROM [dbo].[Enum];
INSERT INTO [dbo].[Enum] (
	[Language],
	[EnumName],
	[ElementValue],
	[ElementName],
	[SortId]
)
SELECT [Language],
		[EnumName],
		[ElementValue],
		[ElementName],
		[SortId]
		FROM [Wuxi_Fandian].[dbo].[Enum]
;
--Freight
PRINT 'Table [dbo].[Freight] ...';
DELETE FROM [dbo].[Freight];
INSERT INTO [dbo].[Freight] (
	[FreightGuid],
	[FrtGrpGuid],
	[TransportMethod],
	[Distance],
	[Freight],
	[AddTime],
	[AddUser]
)
SELECT [FreightGuid],
		[FrtGrpGuid],
		[TransportMethod],
		[Distance],
		[Freight],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[FreightRate]
;
--FreightGroup
PRINT 'Table [dbo].[FreightGroup] ...';
DELETE FROM [dbo].[FreightGroup];
INSERT INTO [dbo].[FreightGroup] (
	[FrtGrpGuid],
	[FrtGrpName],
	[IsDefault],
	[AddTime],
	[AddUser]
)
SELECT [FrtGrpGuid],
		[FrtGrpName],
		[IsDefault],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[FreightGroup]
;
--IssueLastVersion
PRINT 'Table [dbo].[IssueLastVersion] ...';
DELETE FROM [dbo].[IssueLastVersion];
INSERT INTO [dbo].[IssueLastVersion] (
	[CityId],
	[OrderGuid],
	[IssueGuid]
)
SELECT @CityId,
		[OrderGuid],
		[IssueGuid]
		FROM [Wuxi_Fandian].[dbo].[IssueLastVersion]
;
--Item
PRINT 'Table [dbo].[Item] ...';
DELETE FROM [dbo].[Item];
INSERT INTO [dbo].[Item] (
	[ItemGuid],
	[CityId],
	[ItemId],
	[ItemName],
	[VendorGuid],
	[SrvCmbGuid],
	[CtgGroupGuid],
	[SortGroupGuid],
	[UnitGuid],
	[UnitPrice],
	[MinOrderQty],
	[SettleDiscount],
	[ItemQty],
	[BoxQty],
	[BoxUnitPrice],
	[IsSetMeal],
	[SetMealType],
	[InputCode],
	[Description],
	[Remark],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [ItemGuid],
		@CityId,
		[ItemId],
		[ItemName],
		[VendorGuid],
		[SrvCmbGuid],
		[CtgGroupGuid],
		[SortGroupGuid],
		[UnitGuid],
		[UnitPrice],
		[MinOrderQty],
		[SettleDiscount],
		[ItemQty],
		[BoxQty],
		[BoxUnitPrice],
		[IsSetMeal],
		[SetMealType],
		[InputCode],
		[Description],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[Item]
;
--ItemProperty
PRINT 'Table [dbo].[ItemProperty] ...';
DELETE FROM [dbo].[ItemProperty];
INSERT INTO [dbo].[ItemProperty] (
	[PropGuid],
	[PropName],
	[PropValue],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [PropGuid],
		[PropName],
		[PropValue],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[ItemProperty]
;
--ItemSelectGroup
PRINT 'Table [dbo].[ItemSelectGroup] ...';
DELETE FROM [dbo].[ItemSelectGroup];
INSERT INTO [dbo].[ItemSelectGroup] (
	[ISGGuid],
	[GroupName],
	[SelectCount],
	[Remark]
)
SELECT [ISGGuid],
		[GroupName],
		[SelectCount],
		[Remark]
		FROM [Wuxi_Fandian].[dbo].[ItemSelectGroup]
;
--ItemSoldOut
PRINT 'Table [dbo].[ItemSoldOut] ...';
DELETE FROM [dbo].[ItemSoldOut];
INSERT INTO [dbo].[ItemSoldOut] (
	[SoldOutGuid],
	[CityId],
	[VendorGuid],
	[VendorName],
	[ItemGuid],
	[ItemName],
	[StartTime],
	[EndTime],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [SoldOutGuid],
		@CityId,
		[VendorGuid],
		[VendorName],
		[ItemGuid],
		[ItemName],
		[StartTime],
		[EndTime],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[ItemSoldOut]
;
--ItemUnit
PRINT 'Table [dbo].[ItemUnit] ...';
DELETE FROM [dbo].[ItemUnit];
INSERT INTO [dbo].[ItemUnit] (
	[UnitGuid],
	[UnitName],
	[DecimalDigits],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [UnitGuid],
		[UnitName],
		[DecimalDigits],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[ItemUnit]
;
--NumberSequence
PRINT 'Table [dbo].[NumberSequence] ...';
DELETE FROM [dbo].[NumberSequence];
INSERT INTO [dbo].[NumberSequence] (
	[NumSeqName],
	[Comment],
	[Format],
	[TableName],
	[ColumnName],
	[PartitionColumn],
	[Disabled]
)
SELECT [NumSeqName],
		[Comment],
		[Format],
		[TableName],
		[ColumnName],
		@PartitionColumn,
		[Disabled]
		FROM [Wuxi_Fandian].[dbo].[NumberSequence]
;
--Order
PRINT 'Table [dbo].[Order] ...';
DELETE FROM [dbo].[Order];
INSERT INTO [dbo].[Order] (
	[OrderGuid],
	[CityId],
	[VersionId],
	[OrderId],
	[StatusId],
	[IsClosed],
	[IsCanceled],
	[SalesGuid],
	[VendorGuid],
	[VendorId],
	[VendorName],
	[VendorCoord],
	[Variable],
	[ItemCount],
	[ItemAmount],
	[BoxQty],
	[BoxAmount],
	[SumAmount],
	[Distance],
	[Freight],
	[FreightOrigin],
	[TotalAmount],
	[PaymentMethod],
	[TransportMethod],
	[ReqTimeStart],
	[TimeDirection],
	[ReqTimeEnd],
	[Remark],
	[InitTime],
	[CreatedTime],
	[AddTime],
	[AddUser],
	[ModifiedTime],
	[ModifiedUser],
	[CustChanged],
	[InfoChanged],
	[ItemChanged]
)
SELECT o.OrderGuid,
		@CityId,
		ov.VersionId,
		o.OrderId,
		ov.StatusId,
		ov.IsClosed,
		ov.IsCanceled,
		o.SalesGuid,
		p.VendorGuid,
		p.VendorId,
		p.VendorName,
		p.Coordinate,
		p.Variable,
		oiv.ItemCount,
		oiv.ItemAmount,
		oiv.BoxQty,
		oiv.BoxAmount,
		oiv.SumAmount,
		fv.Distance,
		fv.Freight,
		fv.FreightOrigin,
		ov.TotalAmount,
		fv.PaymentMethod,
		fv.TransportMethod,
		fv.ReqTimeStart,
		fv.TimeDirection,
		fv.ReqTimeEnd,
		fv.Remark,
		o.AddTime,
		o.AddTime,
		o.AddTime,
		o.AddUser,
		NULL,
		NULL,
		@CustChanged,
		ov.FreightChanged,
		@ItemChanged
	FROM [WuXi_Fandian].[dbo].[Order] AS o
		INNER JOIN [WuXi_Fandian].[dbo].[OrderVersion] AS ov
			ON ov.OrderGuid=o.OrderGuid
		INNER JOIN [WuXi_Fandian].[dbo].[OrderLastVersion] AS olv
			ON olv.OrdVerGuid=ov.OrdVerGuid
		INNER JOIN [WuXi_Fandian].[dbo].[OrderItemVersion] AS oiv
			ON oiv.OIVGuid=ov.OIVGuid
		INNER JOIN [WuXi_Fandian].[dbo].[SalesVersion] AS sv
			ON sv.SalesVerGuid=ov.SalesVerGuid
		INNER JOIN [WuXi_Fandian].[dbo].[Sales] AS s
			ON s.SalesGuid=o.SalesGuid
		INNER JOIN [WuXi_Fandian].[dbo].[FreightVersion] AS fv
			ON fv.FrtVerGuid=ov.FrtVerGuid
		INNER JOIN [WuXi_Fandian].[dbo].[Purchase] AS p
			ON p.PurchGuid=o.PurchGuid
		INNER JOIN [WuXi_Fandian].[dbo].[Vendor] AS v
			ON v.VendorGuid=p.VendorGuid
;
--OrderCancel
PRINT 'Table [dbo].[OrderCancel] ...';
DELETE FROM [dbo].[OrderCancel];
INSERT INTO [dbo].[OrderCancel] (
	[OrderGuid],
	[CityId],
	[CancelGuid],
	[Remark],
	[AddUser],
	[AddTime]
)
SELECT [OrderGuid],
		@CityId,
		[CancelGuid],
		[Remark],
		[AddUser],
		[AddTime]
		FROM [Wuxi_Fandian].[dbo].[OrderCancel]
;
--OrderIssue
DELETE FROM [dbo].[OrderIssue];
/*
INSERT INTO [dbo].[OrderIssue] (
	[IssueGuid],
	[CityId],
	[OrderGuid],
	[VersionId],
	[StatusId],
	[VendorGuid],
	[VendorName],
	[ItemCount],
	[ItemAmount],
	[BoxAmount],
	[SumAmount],
	[OrdVerGuid],
	[ContactMethod],
	[FinishTime],
	[Remark],
	[AddTime],
	[AddUser]
)
SELECT [IssueGuid],
		@CityId,
		[OrderGuid],
		[VersionId],
		[StatusId],
		[VendorGuid],
		[VendorName],
		[ItemCount],
		[ItemAmount],
		[BoxAmount],
		[SumAmount],
		[OrdVerGuid],
		[ContactMethod],
		[FinishTime],
		[Remark],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[OrderIssue]
;*/
--OrderItem
PRINT 'Table [dbo].[OrderItem] ...';
DELETE FROM [dbo].[OrderItem];
INSERT INTO [dbo].[OrderItem] (
	[OrdItemGuid],
	[CityId],
	[OrderGuid],
	[LineIndex],
	[ItemGuid],
	[ItemId],
	[ItemName],
	[ItemPrice],
	[ItemQty],
	[MinOrderQty],
	[ItemUnit],
	[ItemAmount],
	[BoxQty],
	[BoxRatioQty],
	[ItemRatioQty],
	[BoxPrice],
	[BoxAmount],
	[TotalAmount],
	[ItemReq],
	[Remark],
	[ItemPriceOrigin],
	[ItemPriceLastModified],
	[ItemQtyLastModified],
	[StatusId],
	[IsClosed],
	[IsCanceled],
	[CancelGuid],
	[AddUser],
	[AddTime]
)
SELECT oi.[OrdItemGuid],
		@CityId,
		oi.[OrderGuid],
		oi.[LineIndex],
		oi.[ItemGuid],
		oi.[ItemId],
		oi.[ItemName],
		oi.[ItemPrice],
		oi.[ItemQty],
		oi.[MinOrderQty],
		oi.[ItemUnit],
		oi.[ItemAmount],
		oi.[BoxQty],
		oi.[BoxRatioQty],
		oi.[ItemRatioQty],
		oi.[BoxPrice],
		oi.[BoxAmount],
		oi.[TotalAmount],
		oi.[ItemReq],
		oi.[Remark],
		oi.[ItemPriceOrigin],
		oi.[ItemPriceLastModified],
		oi.[ItemQtyLastModified],
		oi.[StatusId],
		oi.[IsClosed],
		oi.[IsCanceled],
		oi.[CancelGuid],
		oi.[AddUser],
		oi.[AddTime]
	FROM [Wuxi_Fandian].[dbo].[Order] AS o
		INNER JOIN [Wuxi_Fandian].[dbo].[OrderVersion] AS ov
			ON ov.OrderGuid=o.OrderGuid
		INNER JOIN [Wuxi_Fandian].[dbo].[OrderLastVersion] AS olv
			ON olv.OrdVerGuid=ov.OrdVerGuid
		INNER JOIN [Wuxi_Fandian].[dbo].[FreightVersion] AS fv
			ON fv.FrtVerGuid=ov.FrtVerGuid
		INNER JOIN [Wuxi_Fandian].[dbo].[Purchase] AS p
			ON p.PurchGuid=o.PurchGuid
		INNER JOIN [Wuxi_Fandian].[dbo].[OrderItemVersion] AS oiv
			ON oiv.OIVGuid=ov.OIVGuid
		INNER JOIN [Wuxi_Fandian].[dbo].[OIV_Item] AS oivi
			ON oivi.OIVGuid=oiv.OIVGuid
		INNER JOIN [Wuxi_Fandian].[dbo].[OrderItem] AS oi
			ON oi.OrdItemGuid=oivi.OrdItemGuid
;
--OrderItemVersion
PRINT 'Table [dbo].[OrderItemVersion] ...';
DELETE FROM [dbo].[OrderItemVersion];
INSERT INTO [dbo].[OrderItemVersion] (
	[OrdItemGuid],
	[CityId],
	[OrderGuid],
	[LineIndex],
	[ItemGuid],
	[ItemId],
	[ItemName],
	[ItemPrice],
	[ItemQty],
	[MinOrderQty],
	[ItemUnit],
	[ItemAmount],
	[BoxQty],
	[BoxRatioQty],
	[ItemRatioQty],
	[BoxPrice],
	[BoxAmount],
	[TotalAmount],
	[ItemReq],
	[Remark],
	[ItemPriceOrigin],
	[ItemPriceLastModified],
	[ItemQtyLastModified],
	[StatusId],
	[IsClosed],
	[IsCanceled],
	[CancelGuid],
	[AddUser],
	[AddTime]
)
SELECT DISTINCT 
		oi.[OrdItemGuid],
		'wx',
		oi.[OrderGuid],
		oi.[LineIndex],
		oi.[ItemGuid],
		oi.[ItemId],
		oi.[ItemName],
		oi.[ItemPrice],
		oi.[ItemQty],
		oi.[MinOrderQty],
		oi.[ItemUnit],
		oi.[ItemAmount],
		oi.[BoxQty],
		oi.[BoxRatioQty],
		oi.[ItemRatioQty],
		oi.[BoxPrice],
		oi.[BoxAmount],
		oi.[TotalAmount],
		oi.[ItemReq],
		oi.[Remark],
		oi.[ItemPriceOrigin],
		oi.[ItemPriceLastModified],
		oi.[ItemQtyLastModified],
		oi.[StatusId],
		oi.[IsClosed],
		oi.[IsCanceled],
		oi.[CancelGuid],
		oi.[AddUser],
		oi.[AddTime]
	FROM [WuXi_FanDian].[dbo].[OrderVersion] AS ov
		INNER JOIN [WuXi_FanDian].[dbo].[OIV_Item] AS oivi
			ON oivi.OIVGuid=ov.OIVGuid
		INNER JOIN [WuXi_FanDian].[dbo].[OrderItem] AS oi
			ON oi.[OrdItemGuid]=oivi.OrdItemGuid
		INNER JOIN [WuXi_FanDian].[dbo].[Order] AS o
			ON o.[OrderGuid]=ov.[OrderGuid]
	WHERE ov.[OrdVerGuid] NOT IN (
		SELECT ov.OrdVerGuid
			FROM [WuXi_FanDian].[dbo].[Order] AS o
				INNER JOIN [WuXi_FanDian].[dbo].[OrderVersion] AS ov
					ON ov.[OrderGuid]=o.[OrderGuid]
				INNER JOIN [WuXi_FanDian].[dbo].[OrderLastVersion] AS olv
					ON olv.[OrdVerGuid]=ov.[OrdVerGuid]
	)
;
--OrderLoss
PRINT 'Table [dbo].[OrderLoss] ...';
DELETE FROM [dbo].[OrderLoss];
INSERT INTO [dbo].[OrderLoss] (
	[LossGuid],
	[CityId],
	[OrderGuid],
	[LossType],
	[LossAmount],
	[Reason],
	[Handling],
	[Handler],
	[Remark],
	[AddTime],
	[AddUser]
)
SELECT [LossGuid],
		@CityId,
		[OrderGuid],
		[LossType],
		[LossAmount],
		[Reason],
		[Handling],
		[Handler],
		[Remark],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[OrderLoss]
;
--OrderOnlinePay
PRINT 'Table [dbo].[OrderOnlinePay] ...';
DELETE FROM [dbo].[OrderOnlinePay];
INSERT INTO [dbo].[OrderOnlinePay] (
	[OrderGuid],
	[PayedMoney],
	[AddTime],
	[PayedVia]
)
SELECT [OrderGuid],
		[PayedMoney],
		[AddTime],
		[PayedVia]
		FROM [Wuxi_Fandian].[dbo].[OrderOnlinePay]
;
--OrderPayment
PRINT 'Table [dbo].[OrderPayment] ...';
DELETE FROM [dbo].[OrderPayment];
INSERT INTO [dbo].[OrderPayment] (
	[PaymentGuid],
	[OrderGuid],
	[Hash],
	[AddTime],
	[BankApi],
	[BankId],
	[PaidMoney],
	[CallbackSign]
)
SELECT [PaymentGuid],
		[OrderGuid],
		[Hash],
		[AddTime],
		[BankApi],
		[BankId],
		[PaidMoney],
		[CallbackSign]
		FROM [Wuxi_Fandian].[dbo].[OrderPayment]
;
--OrderStatus
PRINT 'Table [dbo].[OrderStatus] ...';
DELETE FROM [dbo].[OrderStatus];
INSERT INTO [dbo].[OrderStatus] (
	[StatusIndex],
	[StatusId],
	[StatusName],
	[Descript],
	[DisplayName],
	[PublicName],
	[StatusType],
	[IsClosed],
	[LossHandle],
	[PrvStatusId],
	[Disabled]
)
SELECT [StatusIndex],
		[StatusId],
		[StatusName],
		[Descript],
		[DisplayName],
		[PublicName],
		[StatusType],
		[IsClosed],
		[LossHandle],
		[PrvStatusId],
		[Disabled]
		FROM [Wuxi_Fandian].[dbo].[OrderStatus]
;
--OrderStatusLog
PRINT 'Table [dbo].[OrderStatusLog] ...';
DELETE FROM [dbo].[OrderStatusLog];
INSERT INTO [dbo].[OrderStatusLog] (
	[StatusLogGuid],
	[CityId],
	[OrderGuid],
	[StatusId],
	[Remark],
	[AddTime],
	[AddUser]
)
SELECT [StatusLogGuid],
		@CityId,
		[OrderGuid],
		[StatusId],
		[Remark],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[OrderStatusLog]
;
--OrderVersion
PRINT 'Table [dbo].[OrderVersion] ...';
DELETE FROM [dbo].[OrderVersion];
INSERT INTO [dbo].[OrderVersion] (
				[OrdVerGuid], [CityId], [OrderGuid], [VersionId], [OrderId], [StatusId],
				[IsClosed], [IsCanceled], [SalesGuid], [SalesVerGuid], [VendorGuid], [VendorId],
				[VendorName], [VendorCoord], [Variable], [OVIGuid], [ItemCount], [ItemAmount],
				[BoxQty], [BoxAmount], [SumAmount], [Distance], [Freight], [FreightOrigin],
				[TotalAmount], [PaymentMethod], [TransportMethod], [ReqTimeStart], [TimeDirection],
				[ReqTimeEnd], [Remark], [CustChanged], [InfoChanged], [ItemChanged], [InitTime],
				[CreatedTime], [AddTime], [AddUser]
			)
SELECT ov.[OrdVerGuid],@CityId,ov.[OrderGuid],ov.[VersionId],o.[OrderId],ov.[StatusId],
			ov.[IsClosed],ov.[IsCanceled],o.[SalesGuid],ov.[SalesVerGuid],p.[VendorGuid],p.[VendorId],
			p.[VendorName],p.[Coordinate],p.[Variable],oiv.[OIVGuid],oiv.[ItemCount],oiv.[ItemAmount],
			oiv.[BoxQty],oiv.[BoxAmount],oiv.[SumAmount],fv.[Distance],fv.[Freight],fv.[FreightOrigin],
			ov.[TotalAmount],fv.[PaymentMethod],fv.[TransportMethod],fv.[ReqTimeStart],fv.[TimeDirection],
			fv.[ReqTimeEnd],fv.[Remark],ov.[CustChanged], ov.[FreightChanged], ov.[ItemChanged],ov.[AddTime],
			ov.[AddTime],ov.[AddTime], ov.[AddUser]
	FROM [Wuxi_Fandian].[dbo].[OrderVersion] AS ov
		INNER JOIN [Wuxi_Fandian].[dbo].[Order] AS o
			ON o.OrderGuid=ov.OrderGuid
		INNER JOIN [Wuxi_Fandian].[dbo].[OrderItemVersion] AS oiv
			ON oiv.OIVGuid=ov.OIVGuid
		INNER JOIN [Wuxi_Fandian].[dbo].[Purchase] AS p
			ON p.PurchGuid=o.PurchGuid
		INNER JOIN [Wuxi_Fandian].[dbo].[FreightVersion] AS fv
			ON fv.FrtVerGuid=ov.FrtVerGuid
		INNER JOIN [Wuxi_Fandian].[dbo].[SalesVersion] AS sv
			ON sv.SalesVerGuid=ov.SalesVerGuid
		INNER JOIN (
			SELECT ov.[OrderGuid], ov.[VersionId], MAX(ov.AddTime) AS [AddTime]
			FROM [WuXi_Fandian].[dbo].[Order] AS o
				INNER JOIN [WuXi_Fandian].[dbo].[OrderVersion] AS ov
					ON ov.[OrderGuid]=o.[OrderGuid]
			GROUP BY ov.[VersionId], ov.[OrderGuid]
		) AS mov
			ON mov.OrderGuid=ov.OrderGuid AND mov.VersionId=ov.VersionId AND ov.AddTime=mov.AddTime
	WHERE ov.[OrdVerGuid] NOT IN (
		SELECT ov.OrdVerGuid
			FROM [WuXi_FanDian].[dbo].[Order] AS o
				INNER JOIN [WuXi_FanDian].[dbo].[OrderVersion] AS ov
					ON ov.[OrderGuid]=o.[OrderGuid]
				INNER JOIN [WuXi_FanDian].[dbo].[OrderLastVersion] AS olv
					ON olv.[OrdVerGuid]=ov.[OrdVerGuid]	
	)
;
--OrderVersionItem
PRINT 'Table [dbo].[OrderVersionItem] ...';
DELETE FROM [dbo].[OrderVersionItem];
INSERT INTO [dbo].[OrderVersionItem] (
	[CityId],
	[OVIGuid],
	[OrdItemGuid]
)
SELECT @CityId,
		[OIVGuid],
		[OrdItemGuid]
		FROM [Wuxi_Fandian].[dbo].[OIV_Item]
;

--PermGroup
PRINT 'Table [dbo].[PermGroup] ...';
DELETE FROM [dbo].[PermGroup];
INSERT INTO [dbo].[PermGroup] (
	[GroupGuid],
	[GroupName],
	[Description],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [GroupGuid],
		[GroupName],
		[Description],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[PermGroup]
;
--PermGroupRights
PRINT 'Table [dbo].[PermGroupRights] ...';
DELETE FROM [dbo].[PermGroupRights];
INSERT INTO [dbo].[PermGroupRights] (
	[PGRGuid],
	[GroupGuid],
	[RightsGuid],
	[SortIndex],
	[RightsValue]
)
SELECT [PGRGuid],
		[GroupGuid],
		[RightsGuid],
		[SortIndex],
		[RightsValue]
		FROM [Wuxi_Fandian].[dbo].[PermGroupRights]
;
--Region
--PRINT 'Table [dbo].[Region] ...';
--DELETE FROM [dbo].[Region];
--INSERT INTO [dbo].[Region] (
--	[RegionGuid],
--	[RegionId],
--	[RegionName],
--	[Level],
--	[RealLevel],
--	[ParentRegion],
--	[CallingCode],
--	[ZipCode],
--	[Disabled],
--	[AddTime],
--	[AddUser]
--)
--SELECT [RegionGuid],
--		[RegionId],
--		[RegionName],
--		[Level],
--		[RealLevel],
--		[ParentRegion],
--		[CallingCode],
--		[ZipCode],
--		[Disabled],
--		[AddTime],
--		[AddUser]
--		FROM [Wuxi_Fandian].[dbo].[Region]
--;
--RegionLevel
--PRINT 'Table [dbo].[RegionLevel] ...';
--DELETE FROM [dbo].[RegionLevel];
--INSERT INTO [dbo].[RegionLevel] (
--	[LevelGuid],
--	[Level],
--	[Name],
--	[IsPrimary],
--	[Disabled],
--	[AddTime],
--	[AddUser]
--)
--SELECT [LevelGuid],
--		[Level],
--		[Name],
--		[IsPrimary],
--		[Disabled],
--		[AddTime],
--		[AddUser]
--		FROM [Wuxi_Fandian].[dbo].[RegionLevel]
--;

--Sales
PRINT 'Table [dbo].[Sales] ...';
DELETE FROM [dbo].[Sales];
DECLARE @sSalesGuid NVARCHAR(50), @sVersionId INT, @sSalesSource NVARCHAR(50), @sSrvGrpGuid NVARCHAR(50), @sSrvGrpName NVARCHAR(50);
DECLARE @sServiceGuid NVARCHAR(50), @sServiceName NVARCHAR(50), @sCustGuid NVARCHAR(50), @sCustName NVARCHAR(50);
DECLARE @sCategory NVARCHAR(50), @sIsNewCust BIT, @sPhoneGuid NVARCHAR(50), @sCallPhone NVARCHAR(50), @sAddressGuid NVARCHAR(50);
DECLARE @sCustAddress NVARCHAR(50), @sCoordGuid NVARCHAR(50), @sCoordName NVARCHAR(50), @sCoordValue GEOGRAPHY, @sReqDate NVARCHAR(50);
DECLARE @sInvoice BIT, @sCommonComment NVARCHAR(50), @sRequestRemark NVARCHAR(50), @sSalesAttribute NVARCHAR(50), @sAddTime NVARCHAR(50);
DECLARE @sAddUser NVARCHAR(50);
DECLARE sCursor CURSOR
	FOR
		SELECT sv.SalesGuid, 
			sv.VersionId,
			s.SalesSource,
			sv.SrvGrpGuid,
			sv.SrvGrpName,
			sv.ServiceGuid,
			sv.ServiceName,
			s.CustGuid,
			sv.CustName,
			sv.Category,
			s.IsNewCust,
			s.PhoneGuid,
			s.CallPhone,
			sv.AddressGuid,
			sv.CustAddress,
			sv.CoordGuid,
			sv.CoordName,
			sv.CoordValue,
			sv.ReqDate,
			sv.Invoice,
			sv.CommonComment,
			sv.RequestRemark,
			sv.SalesAttribute,
			sv.AddTime,
			sv.AddUser
			FROM [Wuxi_Fandian].[dbo].[Order] AS o
				INNER JOIN [Wuxi_Fandian].[dbo].[OrderVersion] AS ov
					ON ov.OrderGuid=o.OrderGuid
				INNER JOIN [Wuxi_Fandian].[dbo].[OrderLastVersion] AS olv
					ON olv.OrdVerGuid=ov.OrdVerGuid
				INNER JOIN [Wuxi_Fandian].[dbo].[SalesVersion] AS sv
					ON sv.SalesVerGuid=ov.SalesVerGuid
				INNER JOIN [Wuxi_Fandian].[dbo].[Sales] AS s
					ON s.SalesGuid=o.SalesGuid
	OPEN sCursor
	FETCH NEXT FROM sCursor
		INTO
			@sSalesGuid, @sVersionId, @sSalesSource, @sSrvGrpGuid, @sSrvGrpName, @sServiceGuid, @sServiceName,
			@sCustGuid, @sCustName, @sCategory, @sIsNewCust, @sPhoneGuid, @sCallPhone, @sAddressGuid, @sCustAddress, @sCoordGuid, 
			@sCoordName, @sCoordValue, @sReqDate, @sInvoice, @sCommonComment, @sRequestRemark, @sSalesAttribute, @sAddTime, @sAddUser
	WHILE @@FETCH_STATUS=0
	BEGIN
		PRINT 'Cursor Sales '+@sSalesGuid
		INSERT INTO [dbo].[Sales] (
			[SalesGuid],
			[CityId],
			[VersionId],
			[CityGuid],
			[AreaGuid],
			[SalesSource],
			[SrvGrpGuid],
			[SrvGrpName],
			[ServiceGuid],
			[ServiceName],
			[CustGuid],
			[CustName],
			[Category],
			[IsNewCust],
			[PhoneGuid],
			[CallPhone],
			[AddressGuid],
			[CustAddress],
			[CoordGuid],
			[CoordName],
			[CoordValue],
			[ReqDate],
			[Paid],
			[Invoice],
			[CommonComment],
			[RequestRemark],
			[SalesAttribute],
			[CreatedTime],
			[AddTime],
			[AddUser]
		) VALUES (
			@sSalesGuid, @CityId, @sVersionId, @CityGuid, @AreaGuid, @sSalesSource, @sSrvGrpGuid, @sSrvGrpName, @sServiceGuid, @sServiceName,
			@sCustGuid, @sCustName, @sCategory, @sIsNewCust, @sPhoneGuid, @sCallPhone, @sAddressGuid, @sCustAddress, @sCoordGuid, @sCoordName, @sCoordValue,
			@sReqDate, @Paid, @sInvoice, @sCommonComment, @sRequestRemark, @sSalesAttribute, @sAddTime, @sAddTime, @sAddUser
		)
		FETCH NEXT FROM sCursor
			INTO
				@sSalesGuid, @sVersionId, @sSalesSource, @sSrvGrpGuid, @sSrvGrpName, @sServiceGuid, @sServiceName,
				@sCustGuid, @sCustName, @sCategory, @sIsNewCust, @sPhoneGuid, @sCallPhone, @sAddressGuid, @sCustAddress, @sCoordGuid, 
				@sCoordName, @sCoordValue, @sReqDate, @sInvoice, @sCommonComment, @sRequestRemark, @sSalesAttribute, @sAddTime, @sAddUser
	END 
	CLOSE sCursor
	DEALLOCATE sCursor;	
	
--SalesAttribute
PRINT 'Table [dbo].[SalesAttribute] ...';
DELETE FROM [dbo].[SalesAttribute];
INSERT INTO [dbo].[SalesAttribute] (
	[AttrGuid],
	[AttributeName],
	[PaymentMethod],
	[Paid],
	[Remark],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [AttrGuid],
		[AttributeName],
		[PaymentMethod],
		[Paid],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[SalesAttribute]
;
--SalesVersion
PRINT 'Table [dbo].[SalesVersion] ...';
DELETE FROM [dbo].[SalesVersion];
INSERT INTO [dbo].[SalesVersion] (
	[SalesVerGuid],
	[CityId],
	[SalesGuid],
	[VersionId],
	[CityGuid],
	[AreaGuid],
	[SalesSource],
	[SrvGrpGuid],
	[SrvGrpName],
	[ServiceGuid],
	[ServiceName],
	[CustGuid],
	[IsNewCust],
	[PhoneGuid],
	[CallPhone],
	[CustName],
	[Category],
	[AddressGuid],
	[CustAddress],
	[CoordGuid],
	[CoordName],
	[CoordValue],
	[ReqDate],
	[Invoice],
	[CommonComment],
	[RequestRemark],
	[SalesAttribute],
	[CreatedTime],
	[AddTime],
	[AddUser]
)
SELECT sv.SalesVerGuid,
		@CityId,
		sv.SalesGuid,
		sv.VersionId,
		@CityGuid,
		@AreaGuid,
		s.SalesSource,
		sv.SrvGrpGuid,
		sv.SrvGrpName,
		sv.ServiceGuid,
		sv.ServiceName,
		s.CustGuid,
		s.IsNewCust,
		s.PhoneGuid,
		s.CallPhone,
		sv.CustName,
		sv.Category,
		sv.AddressGuid,
		sv.CustAddress,
		sv.CoordGuid,
		sv.CoordName,
		sv.CoordValue,
		sv.ReqDate,
		sv.Invoice,
		sv.CommonComment,
		sv.RequestRemark,
		sv.SalesAttribute,
		s.CreatedTime,
		sv.AddTime,
		sv.AddUser
		FROM [Wuxi_Fandian].[dbo].[SalesVersion] AS sv
			INNER JOIN [Wuxi_Fandian].[dbo].[Sales] AS s
				ON s.SalesGuid=sv.SalesGuid
;
--Service
PRINT 'Table [dbo].[Service] ...';
DELETE FROM [dbo].[Service];
INSERT INTO [dbo].[Service] (
	[ServiceGuid],
	[CityId],
	[SrvGrpGuid],
	[ServiceName],
	[StartTime],
	[EndTime],
	[SpanDays],
	[StartOffsetMinutes],
	[EndOffsetMinutes],
	[FrtGrpGuid],
	[Remark],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [ServiceGuid],
		@CityId,
		[SrvGrpGuid],
		[ServiceName],
		[StartTime],
		[EndTime],
		[SpanDays],
		[StartOffsetMinutes],
		[EndOffsetMinutes],
		[FrtGrpGuid],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[Service]
;
--ServiceCombin
PRINT 'Table [dbo].[ServiceCombin] ...';
DELETE FROM [dbo].[ServiceCombin];
INSERT INTO [dbo].[ServiceCombin] (
	[SrvCmbGuid],
	[CityId],
	[CityGuid],
	[AreaGuid],
	[SrvCmbName],
	[Remark],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [SrvCmbGuid],
		@CityId,
		@CityGuid,
		@AreaGuid,
		[SrvCmbName],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[ServiceCombin]	
;
--ServiceCombinMember
PRINT 'Table [dbo].[ServiceCombinMember] ...';
DELETE FROM [dbo].[ServiceCombinMember];
INSERT INTO [dbo].[ServiceCombinMember] (
	[SCMGuid],
	[CityId],
	[SrvCmbGuid],
	[SortIndex],
	[ServiceGuid]
)
SELECT [SCMGuid],
		@CityId,
		[SrvCmbGuid],
		[SortIndex],
		[ServiceGuid]
		FROM [Wuxi_Fandian].[dbo].[ServiceCombinMember]
;
--ServiceGroup
PRINT 'Table [dbo].[ServiceGroup] ...';
DELETE FROM [dbo].[ServiceGroup];
INSERT INTO [dbo].[ServiceGroup] (
	[SrvGrpGuid],
	[CityId],
	[SrvGrpName],
	[CityGuid],
	[AreaGuid],
	[Remark],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [SrvGrpGuid],
		@CityId,
		[SrvGrpName],
		@CityGuid,
		@AreaGuid,
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[ServiceGroup]
;
--SortGroup
PRINT 'Table [dbo].[SortGroup] ...';
DELETE FROM [dbo].[SortGroup];
INSERT INTO [dbo].[SortGroup] (
	[SortGroupGuid],
	[SortGroupName],
	[TargetObject],
	[SortIndex],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [SortGroupGuid],
		[SortGroupName],
		[TargetObject],
		[SortIndex],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[SortGroup]
;
--SystemOption
PRINT 'Table [dbo].[SystemOption] ...';
DELETE FROM [dbo].[SystemOption];
SET IDENTITY_INSERT [dbo].[SystemOption] ON;
INSERT INTO [dbo].[SystemOption] (
	[IndexId],
	[OptionId],
	[OptionName],
	[ValueType],
	[BooleanValue],
	[IntValue],
	[FloatValue],
	[MoneyValue],
	[DateTimeValue],
	[DateValue],
	[TimeValue],
	[StringValue],
	[TextValue],
	[GuidValue]
)
SELECT [IndexId],
		[OptionId],
		[OptionName],
		[ValueType],
		[BooleanValue],
		[IntValue],
		[FloatValue],
		[MoneyValue],
		[DateTimeValue],
		[DateValue],
		[TimeValue],
		[StringValue],
		[TextValue],
		[GuidValue]
		FROM [Wuxi_Fandian].[dbo].[SystemOption]
;
SET IDENTITY_INSERT [dbo].[SystemOption] OFF;
--User
PRINT 'Table [dbo].[User] ...';
DELETE FROM [dbo].[User];
INSERT INTO [dbo].[User] (
	[UserGuid],
	[UserId],
	[UserName],
	[Password],
	[SrvGrpGuid],
	[IsAdmin],
	[PermGroupGuid],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [UserGuid],
		[UserId],
		[UserName],
		[Password],
		[SrvGrpGuid],
		[IsAdmin],
		[PermGroupGuid],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[User]
;
--Vendor
PRINT 'Table [dbo].[Vendor] ...';
DELETE FROM [dbo].[Vendor];
INSERT INTO [dbo].[Vendor] (
	[VendorGuid],
	[CityId],
	[CityGuid],
	[AreaGuid],
	[RegionGuid],
	[VendorId],
	[VendorName],
	[ServiceStatus],
	[CtgGroupGuid],
	[SortGroupGuid],
	[CorpGuid],
	[SettleDiscount],
	[ProvideInvoice],
	[InvoiceMaxLimit],
	[TransportMethod],
	[IssueMethod],
	[OrderMinutes],
	[InputCode],
	[Description],
	[Remark],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [VendorGuid],
		@CityId,
		@CityGuid,
		@AreaGuid,
		[RegionGuid],
		[VendorId],
		[VendorName],
		[ServiceStatus],
		[CtgGroupGuid],
		[SortGroupGuid],
		[CorpGuid],
		[SettleDiscount],
		[ProvideInvoice],
		[InvoiceMaxLimit],
		[TransportMethod],
		[IssueMethod],
		[OrderMinutes],
		[InputCode],
		[Description],
		[Remark],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[Vendor]
;
--VendorAddress
PRINT 'Table [dbo].[VendorAddress] ...';
DELETE FROM [dbo].[VendorAddress];
INSERT INTO [dbo].[VendorAddress] (
	[VendorGuid],
	[CityId],
	[Address],
	[CoordType],
	[Longitude],
	[Latitude],
	[CoordValue],
	[Variable],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [VendorGuid],
		@CityId,
		[Address],
		[CoordType],
		[Longitude],
		[Latitude],
		[CoordValue],
		[Variable],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[VendorAddress]
;
--VendorContactMethod
PRINT 'Table [dbo].[VendorContactMethod] ...';
DELETE FROM [dbo].[VendorContactMethod];
INSERT INTO [dbo].[VendorContactMethod] (
	[VCMGuid],
	[CityId],
	[VendorGuid],
	[ContactMethod],
	[Number],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [VCMGuid],
		@CityId,
		[VendorGuid],
		[ContactMethod],
		[Number],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[VendorContactMethod]
;
--VendorContactPerson
PRINT 'Table [dbo].[VendorContactPerson] ...';
DELETE FROM [dbo].[VendorContactPerson];
INSERT INTO [dbo].[VendorContactPerson] (
	[VCPGuid],
	[CityId],
	[VendorGuid],
	[Name],
	[Title],
	[CellPhone],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [VCPGuid],
		@CityId,
		[VendorGuid],
		[Name],
		[Title],
		[CellPhone],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[VendorContactPerson]
;
--VendorCorporate
PRINT 'Table [dbo].[VendorCorporate] ...';
DELETE FROM [dbo].[VendorCorporate];
INSERT INTO [dbo].[VendorCorporate] (
	[CorpGuid],
	[CityId],
	[CorpName],
	[AddTime],
	[AddUser]
)
SELECT [CorpGuid],
		@CityId,
		[CorpName],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[VendorCorporate]
;
--VendorServiceTime
PRINT 'Table [dbo].[VendorServiceTime] ...';
DELETE FROM [dbo].[VendorServiceTime];
INSERT INTO [dbo].[VendorServiceTime] (
	[VSTGuid],
	[CityId],
	[VendorGuid],
	[DayType],
	[StartTime],
	[EndTime],
	[SpanDays],
	[Hours],
	[Disabled],
	[AddTime],
	[AddUser]
)
SELECT [VSTGuid],
		@CityId,
		[VendorGuid],
		[DayType],
		[StartTime],
		[EndTime],
		[SpanDays],
		[Hours],
		[Disabled],
		[AddTime],
		[AddUser]
		FROM [Wuxi_Fandian].[dbo].[VendorServiceTime]
;
--W_AddressBook
PRINT 'Table [dbo].[W_AddressBook] ...';
DELETE FROM [dbo].[W_AddressBook];
INSERT INTO [dbo].[W_AddressBook] (
	[ABGuid],
	[Title],
	[CustGuid],
	[IsDefault],
	[Address],
	[OrderNo],
	[CoordGuid],
	[CreateTime],
	[Contactor],
	[Phone],
	[CityId]
)
SELECT NEWID(),
		[Title],
		[CustGuid],
		[IsDefault],
		[Address],
		[OrderNo],
		[CoordGuid],
		[CreateTime],
		[Contactor],
		[Phone],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_AddressBook]
;
--W_ApiKeys
PRINT 'Table [dbo].[W_ApiKeys] ...';
DELETE FROM [dbo].[W_ApiKeys];
SET IDENTITY_INSERT [dbo].[W_ApiKeys] ON;
INSERT INTO [dbo].[W_ApiKeys] (
	[ApiKey],
	[Owner],
	[Id],
	[VisitsPerHour],
	[VisitsPerDay],
	[AddTime]
)
SELECT [ApiKey],
		[Owner],
		[Id],
		[VisitsPerHour],
		[VisitsPerDay],
		[AddTime]
		FROM [Wuxi_Fandian].[dbo].[W_ApiKeys]
;
SET IDENTITY_INSERT [dbo].[W_ApiKeys] OFF;
--W_Article
PRINT 'Table [dbo].[W_Article] ...';
DELETE FROM [dbo].[W_Article];
INSERT INTO [dbo].[W_Article] (
	[Title],
	[CategoryId],
	[Detail],
	[PubTime],
	[StartTime],
	[EndTime],
	[OrderNo],
	[Views],
	[PubFlag],
	[AttachHash],
	[FirstAttach],
	[RegionGuid],
	[CityId]
)
SELECT [Title],
		[CategoryId]+100,
		[Detail],
		[PubTime],
		[StartTime],
		[EndTime],
		[OrderNo],
		[Views],
		[PubFlag],
		[AttachHash],
		[FirstAttach],
		@AreaGuid,
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_Article]
;
--W_ArticleCategory
PRINT 'Table [dbo].[W_ArticleCategory] ...';
DELETE FROM [dbo].[W_ArticleCategory];
SET IDENTITY_INSERT [dbo].[W_ArticleCategory] ON;
INSERT INTO [dbo].[W_ArticleCategory] (
	[CategoryId],
	[CategoryName],
	[OrderNo],
	[CityId]
)
SELECT [CategoryId]+100,
		[CategoryName],
		[OrderNo],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_ArticleCategory]
;
SET IDENTITY_INSERT [dbo].[W_ArticleCategory] OFF;
--W_Attachment
PRINT 'Table [dbo].[W_Attachment] ...';
DELETE FROM [dbo].[W_Attachment];
INSERT INTO [dbo].[W_Attachment] (
	[FileId],
	[Name],
	[MimeType],
	[Size],
	[UploadTime],
	[Description],
	[Uid],
	[ReadTimes],
	[Hash],
	[Ext],
	[OrderNo],
	[Usage],
	[Width],
	[Height],
	[CityId]
)
SELECT [FileId],
		[Name],
		[MimeType],
		[Size],
		[UploadTime],
		[Description],
		[Uid],
		[ReadTimes],
		[Hash],
		[Ext],
		[OrderNo],
		[Usage],
		[Width],
		[Height],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_Attachment]
;
--W_AttachmentData
PRINT 'Table [dbo].[W_AttachmentData] ...';
DELETE FROM [dbo].[W_AttachmentData];
INSERT INTO [dbo].[W_AttachmentData] (
	[FileId],
	[Data]
)
SELECT [FileId],
		[Data]
		FROM [Wuxi_Fandian].[dbo].[W_AttachmentData]
;
--W_BaiduPlaceLog
PRINT 'Table [dbo].[W_BaiduPlaceLog] ...';
DELETE FROM [dbo].[W_BaiduPlaceLog];
INSERT INTO [dbo].[W_BaiduPlaceLog] (
	[Address],
	[Longitude],
	[Latitude],
	[AddTime],
	[Name],
	[Phone],
	[CityId]
)
SELECT [Address],
		[Longitude],
		[Latitude],
		[AddTime],
		[Name],
		[Phone],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_BaiduPlaceLog]
;
--W_CoordForBaidu
PRINT 'Table [dbo].[W_CoordForBaidu] ...';
DELETE FROM [dbo].[W_CoordForBaidu];
INSERT INTO [dbo].[W_CoordForBaidu] (
	[CoordGuid],
	[Longitude],
	[Latitude],
	[CoordValue],
	[CityId]
)
SELECT [CoordGuid],
		[Longitude],
		[Latitude],
		[CoordValue],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_CoordForBaidu]
;
--W_FavoritedItems
PRINT 'Table [dbo].[W_FavoritedItems] ...';
DELETE FROM [dbo].[W_FavoritedItems];
INSERT INTO [dbo].[W_FavoritedItems] (
	[CustGuid],
	[ItemGuid],
	[CreateTime],
	[CityId]
)
SELECT [CustGuid],
		[ItemGuid],
		[CreateTime],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_FavoritedItems]
;
--W_FavoritedVendors
PRINT 'Table [dbo].[W_FavoritedVendors] ...';
DELETE FROM [dbo].[W_FavoritedVendors];
INSERT INTO [dbo].[W_FavoritedVendors] (
	[CustGuid],
	[VendorGuid],
	[CreateTime],
	[CityId]
)
SELECT [CustGuid],
		[VendorGuid],
		[CreateTime],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_FavoritedVendors]
;
--W_Feedback
PRINT 'Table [dbo].[W_Feedback] ...';
DELETE FROM [dbo].[W_Feedback];
INSERT INTO [dbo].[W_Feedback] (
	[CustGuid],
	[Username],
	[CreateTime],
	[Content],
	[OrderNo],
	[DisplayFlag],
	[ReplyContent],
	[ReplyTime],
	[Title],
	[IpAddress],
	[RegionGuid],
	[CityId]
)
SELECT [CustGuid],
		[Username],
		[CreateTime],
		[Content],
		[OrderNo],
		[DisplayFlag],
		[ReplyContent],
		[ReplyTime],
		[Title],
		[IpAddress],
		@AreaGuid,
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_Feedback]
;
--W_ItemExtend
PRINT 'Table [dbo].[W_ItemExtend] ...';
DELETE FROM [dbo].[W_ItemExtend];
INSERT INTO [dbo].[W_ItemExtend] (
	[ItemGuid],
	[HasLogo],
	[IsRec],
	[IsTuan],
	[Sales],
	[CityId],
	[Persisted],
	[Detail],
	[LongTitle]
)
SELECT [ItemGuid],
		[HasLogo],
		[IsRec],
		[IsTuan],
		[Sales],
		@CityId,
		[Persisted],
		[Detail],
		[LongTitle]
		FROM [Wuxi_Fandian].[dbo].[W_ItemExtend]
;
--W_OrderAnounce
PRINT 'Table [dbo].[W_OrderAnnounce] ...';
DELETE FROM [dbo].[W_OrderAnnounce];
INSERT INTO [dbo].[W_OrderAnnounce] (
	[Content],
	[AddTime],
	[RegionGuid],
	[CityId]
)
SELECT [Content],
		[AddTime],
		@AreaGuid,
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_OrderAnnounce]
;
--W_OrderHash
PRINT 'Table [dbo].[W_OrderHash] ...';
DELETE FROM [dbo].[W_OrderHash];
INSERT INTO [dbo].[W_OrderHash] (
	[CityId],
	[OrderGuid],
	[Hash],
	[CreateTime],
	[PayMethod],
	[Payed],
	[BankApi],
	[BankId],
	[PayedMoney]
)
SELECT @CityId,
		[OrderGuid],
		[Hash],
		[CreateTime],
		[PayMethod],
		[Payed],
		[BankApi],
		[BankId],
		[PayedMoney]
		FROM [Wuxi_Fandian].[dbo].[W_OrderHash]
;
--W_ResetPasswordHash
PRINT 'Table [dbo].[W_ResetPasswordHash] ...';
DELETE FROM [dbo].[W_ResetPasswordHash];
INSERT INTO [dbo].[W_ResetPasswordHash] (
	[Hash],
	[CustGuid],
	[CreateTime],
	[UsedTime],
	[CityId]
)
SELECT [Hash],
		[CustGuid],
		[CreateTime],
		[UsedTime],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_ResetPasswordHash]
;
--W_SearchLogs
PRINT 'Table [dbo].[W_SearchLogs] ...';
DELETE FROM [dbo].[W_SearchLogs];
INSERT INTO [dbo].[W_SearchLogs] (
	[Keywords],
	[CustGuid],
	[Results],
	[Timeline],
	[CityId]
)
SELECT [Keywords],
		[CustGuid],
		[Results],
		[Timeline],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_SearchLogs]
;
--W_SystemLog
PRINT 'Table [dbo].[W_SystemLog] ...';
DELETE FROM [dbo].[W_SystemLog];
INSERT INTO [dbo].[W_SystemLog] (
	[Uid],
	[ActionTime],
	[Url],
	[Memo],
	[ActionType],
	[Ip]
)
SELECT [Uid],
		[ActionTime],
		[Url],
		[Memo],
		[ActionType],
		[Ip]
		FROM [Wuxi_Fandian].[dbo].[W_SystemLog]
;
--W_SystemVars
PRINT 'Table [dbo].[W_SystemVars] ...';
DELETE FROM [dbo].[W_SystemVars];
INSERT INTO [dbo].[W_SystemVars] (
	[DataKey],
	[DataValue],
	[LastUpdate],
	[RegionGuid],
	[CityId]
)
SELECT [DataKey],
		[DataValue],
		[LastUpdate],
		@AreaGuid,
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_SystemVars]
;
--W_SysUser
PRINT 'Table [dbo].[W_SysUser] ...';
DELETE FROM [dbo].[W_SysUser];
INSERT INTO [dbo].[W_SysUser] (
	[Username],
	[Password],
	[LastLogin],
	[Sysrights]
)
SELECT [Username],
		[Password],
		[LastLogin],
		[Sysrights]
		FROM [Wuxi_Fandian].[dbo].[W_SysUser]
;
--W_TecentConnectToken
PRINT 'Table [dbo].[W_TencentConnectToken] ...';
DELETE FROM [dbo].[W_TencentConnectToken];
INSERT INTO [dbo].[W_TencentConnectToken] (
	[CustGuid],
	[Token],
	[OpenID],
	[LastUpdate],
	[CityId]
)
SELECT [CustGuid],
		[Token],
		[OpenID],
		[LastUpdate],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_TencentConnectToken]
;
--W_Users
PRINT 'Table [dbo].[W_Users] ...';
DELETE FROM [dbo].[W_Users];
INSERT INTO [dbo].[W_Users] (
	[CustGuid],
	[Username],
	[Password],
	[Avatar],
	[LastLogin],
	[Address],
	[Qq],
	[Msn],
	[Homepage],
	[CityId]
)
SELECT [CustGuid],
		[Username],
		[Password],
		[Avatar],
		[LastLogin],
		[Address],
		[Qq],
		[Msn],
		[Homepage],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_Users]
;
--W_VendorExtend
PRINT 'Table [dbo].[W_VendorExtend] ...';
DELETE FROM [dbo].[W_VendorExtend];
INSERT INTO [dbo].[W_VendorExtend] (
	[VendorGuid],
	[Views],
	[Favorites],
	[SmallLogo],
	[BigLogo],
	[AverageCost],
	[HotRate],
	[HasLogo],
	[IsRec],
	[IsIdxRec],
	[OrderNo],
	[CityId]
)
SELECT [VendorGuid],
		[Views],
		[Favorites],
		[SmallLogo],
		[BigLogo],
		[AverageCost],
		[HotRate],
		[HasLogo],
		[IsRec],
		[IsIdxRec],
		[OrderNo],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_VendorExtend]
;
--W_WeiboOauthToken
PRINT 'Table [dbo].[W_WeiboOauthToken] ...';
DELETE FROM [dbo].[W_WeiboOauthToken];
INSERT INTO [dbo].[W_WeiboOauthToken] (
	[CustGuid],
	[WeiboUid],
	[Token],
	[Expires],
	[LastUpdate],
	[CityId]
)
SELECT [CustGuid],
		[WeiboUid],
		[Token],
		[Expires],
		[LastUpdate],
		@CityId
		FROM [Wuxi_Fandian].[dbo].[W_WeiboOauthToken]
;

--Fix Order/OrderVersion
UPDATE [dbo].[Order] SET [InfoChanged]=0 WHERE [VersionId]=0;
UPDATE [dbo].[OrderVersion] SET [InfoChanged]=0 WHERE [VersionId]=0;

--商圈
PRINT 'Fix data for 商圈 ...';
DECLARE @VendorGuid NVARCHAR(50), @BizArea NVARCHAR(50);
DECLARE baCursor CURSOR
	FOR
		SELECT v.VendorGuid, (r.RegionName+','+c.CtgName) AS [BizArea]
			FROM [Wuxi_Fandian].[dbo].[Vendor] AS v
				INNER JOIN [Wuxi_Fandian].[dbo].[Region] AS r
					ON r.RegionGuid=v.RegionGuid
				INNER JOIN [Wuxi_Fandian].[dbo].[CategoryGroup] AS cg
					ON cg.CtgGroupGuid=v.CtgGroupGuid
				INNER JOIN [Wuxi_Fandian].[dbo].[CategoryGroupMember] AS cgm
					ON cgm.CtgGroupGuid=cg.CtgGroupGuid
				INNER JOIN [Wuxi_Fandian].[dbo].[Category] AS c
					ON c.CtgGuid=cgm.CtgGuid
				INNER JOIN [Wuxi_Fandian].[dbo].[CategoryStandard] AS cs
					ON cs.CtgStdGuid=c.CtgStdGuid
		WHERE cs.CtgStdName=N'商圈'
	OPEN baCursor
	FETCH NEXT FROM baCursor 
		INTO @VendorGuid, @BizArea
	WHILE @@FETCH_STATUS=0
	BEGIN
		PRINT 'Cursor BizArea '+@BizArea
		UPDATE [dbo].[W_VendorExtend] SET [BizArea]=@BizArea WHERE [VendorGuid]=@VendorGuid
		FETCH NEXT FROM baCursor 
			INTO @VendorGuid, @BizArea
	END 
	CLOSE baCursor
	DEALLOCATE baCursor
	
PRINT ''
PRINT ''
PRINT '=========================================================='
PRINT 'DONE'
PRINT '=========================================================='
PRINT ''
PRINT ''