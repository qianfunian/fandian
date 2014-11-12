USE [wxfd_allcity_new];

UPDATE [dbo].[W_AddressBook] SET [ABGuid]=NEWID();
ALTER TABLE [dbo].[W_AddressBook] DROP CONSTRAINT PK_AddressBook;
ALTER TABLE [dbo].[W_AddressBook]
	ALTER COLUMN [ABGuid] UNIQUEIDENTIFIER NOT NULL;
ALTER TABLE [dbo].[W_AddressBook] WITH NOCHECK 
	ADD CONSTRAINT PK_ABGuid PRIMARY KEY CLUSTERED ([ABGuid]);
DROP INDEX IX_AddressBook ON [dbo].[W_AddressBook];