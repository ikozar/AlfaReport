--
-- PostgreSQL database dump
--

-- Started on 2008-01-18 13:31:02

SET client_encoding = 'WIN1251';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

set search_path to realiz;

/*==============================================================*/
/* DBMS name:      PostgreSQL 8                                 */
/* Created on:     18.01.2008 15:24:20                          */
/*==============================================================*/


drop table assortment cascade;

drop table personal cascade;

drop table realizations cascade;

drop table store cascade;

drop table subdivision cascade;

drop table support cascade;

drop table teritory cascade;

drop table type_assortment cascade;

drop table type_subdiv cascade;

drop table vendor cascade;


/*==============================================================*/
/* Table: personal                                              */
/*==============================================================*/
create table personal (
   id_pers              SERIAL               not null,
   id_subdiv            INT4                 not null,
   fio_pers             CHAR(50)             not null,
   pr_sex               INT2                 not null,
   pr_rukovod           INT2                 not null default 0,
   date_rozd            DATE                 not null,
   constraint PK_PERSONAL primary key (id_pers)
);

/*==============================================================*/
/* Table: realizations                                          */
/*==============================================================*/
create table realizations (
   id_wares             INT4                 null,
   id_pers              INT4                 not null,
   date_realiz          DATE                 not null,
   num_realiz           FLOAT4               not null default '1.0',
   sum_realiz           FLOAT4               not null,
   pc_discount          INT2                 not null default 0,
   price                FLOAT4               not null
);

/*==============================================================*/
/* Table: store                                                 */
/*==============================================================*/
create table store (
   id_store             INT4                 not null,
   id_ter               INT4                 not null,
   naim_store           CHAR(30)             not null,
   constraint PK_STORE primary key (id_store)
);

/*==============================================================*/
/* Table: subdivision                                           */
/*==============================================================*/
create table subdivision (
   id_subdiv            INT4                 not null,
   id_store             INT4                 not null,
   id_subdiv_parent     INT4                 not null,
   naim_subdiv          CHAR(30)             not null,
   id_type_subdiv       INT4                 not null,
   id_type_wares        INT4                 null,
   rub_subdiv           CHAR(20)             not null,
   constraint PK_SUBDIVISION primary key (id_subdiv)
);

/*==============================================================*/
/* Table: support                                               */
/*==============================================================*/
create table support (
   typ                  CHAR                 not null default 'R',
   id                   INT4                 not null,
   id_parent            INT4                 not null,
   naim                 VARCHAR(50)          not null,
   pnom						INT2						not null,
   id_gen               VARCHAR(20)          not null,
   to_level             CHAR(20)             not null default '',
   constraint PK_SUPPORT primary key (id)
);

/*==============================================================*/
/* Table: teritory                                              */
/*==============================================================*/
create table teritory (
   id_ter               INT4                 not null,
   id_ter_parent        INT4                 not null default 0,
   naim_ter             CHAR(40)             not null,
   pr_ter               INT2                 not null,
   rub_terit            CHAR(12)             not null,
   constraint PK_TERITORY primary key (id_ter)
);

/*==============================================================*/
/* Table: type_assortment                                       */
/*==============================================================*/
create table type_wares (
   id_type_wares        INT4                 not null,
   id_type_wares_parent INT4                 not null,
   naim_type_wares      CHAR(40)             not null,
   rub_type_wares       CHAR(12)             not null,
   constraint PK_TYPE_WARES primary key (id_type_wares)
);

/*==============================================================*/
/* Table: type_subdiv                                           */
/*==============================================================*/
create table type_subdiv (
   id_type_subdiv       INT4                 not null,
   naim_type_subdiv     CHAR(30)             not null,
   constraint PK_TYPE_SUBDIV primary key (id_type_subdiv)
);

/*==============================================================*/
/* Table: vendor                                                */
/*==============================================================*/
create table vendor (
   id_vendor            INT4                 not null,
   id_ter               INT4                 not null,
   naim_vendor          CHAR(30)             not null,
   constraint PK_VENDOR primary key (id_vendor)
);

/*==============================================================*/
/* Table: wares                                                 */
/*==============================================================*/
create table wares (
   id_wares             SERIAL               not null,
   id_vendor            INT4                 not null,
   id_type_wares        INT4                 null,
   naim_wares           CHAR(30)             not null,
   price_wares          FLOAT4               not null,
   constraint PK_WARES primary key (id_wares)
);

/*
alter table personal
   drop constraint FK_PERSONAL_REFERENCE_SUBDIVIS;

alter table realizations
   drop constraint FK_REALIZAT_REFERENCE_WARES;

alter table realizations
   drop constraint FK_REALIZAT_REFERENCE_PERSONAL;

alter table store
   drop constraint FK_STORE_REFERENCE_TERITORY;

alter table subdivision
   drop constraint FK_SUBDIVIS_REFERENCE_SUBDIVIS;

alter table subdivision
   drop constraint FK_SUBDIVIS_REFERENCE_TYPE_SUB;

alter table subdivision
   drop constraint FK_SUBDIVIS_REFERENCE_TYPE_ASS;

alter table subdivision
   drop constraint FK_SUBDIVIS_REFERENCE_STORE;

alter table support
   drop constraint FK_SUPPORT_REFERENCE_SUPPORT;

alter table teritory
   drop constraint FK_TERITORY_REFERENCE_TERITORY;

alter table type_wares
   drop constraint FK_TYPE_ASS_REFERENCE_TYPE_ASS;

alter table vendor
   drop constraint FK_VENDOR_REFERENCE_TERITORY;

alter table wares
   drop constraint FK_WARES_REFERENCE_TYPE_ASS;

alter table wares
   drop constraint FK_WARES_REFERENCE_VENDOR;
*/

alter table personal
   add constraint FK_PERSONAL_REFERENCE_SUBDIVIS foreign key (id_subdiv)
      references subdivision (id_subdiv)
      on delete restrict on update restrict;

alter table realizations
   add constraint FK_REALIZAT_REFERENCE_WARES foreign key (id_wares)
      references wares (id_wares)
      on delete restrict on update restrict;

alter table realizations
   add constraint FK_REALIZAT_REFERENCE_PERSONAL foreign key (id_pers)
      references personal (id_pers)
      on delete restrict on update restrict;

alter table store
   add constraint FK_STORE_REFERENCE_TERITORY foreign key (id_ter)
      references teritory (id_ter)
      on delete restrict on update restrict;

alter table subdivision
   add constraint FK_SUBDIVIS_REFERENCE_SUBDIVIS foreign key (id_subdiv_parent)
      references subdivision (id_subdiv)
      on delete restrict on update restrict;

alter table subdivision
   add constraint FK_SUBDIVIS_REFERENCE_TYPE_SUB foreign key (id_type_subdiv)
      references type_subdiv (id_type_subdiv)
      on delete restrict on update restrict;

alter table subdivision
   add constraint FK_SUBDIVIS_REFERENCE_TYPE_ASS foreign key (id_type_wares)
      references type_wares (id_type_wares)
      on delete restrict on update restrict;

alter table subdivision
   add constraint FK_SUBDIVIS_REFERENCE_STORE foreign key (id_store)
      references store (id_store)
      on delete restrict on update restrict;

alter table support
   add constraint FK_SUPPORT_REFERENCE_SUPPORT foreign key (id_parent)
      references support (id)
      on delete restrict on update restrict;

alter table teritory
   add constraint FK_TERITORY_REFERENCE_TERITORY foreign key (id_ter_parent)
      references teritory (id_ter)
      on delete restrict on update restrict;

alter table type_wares
   add constraint FK_TYPE_ASS_REFERENCE_TYPE_ASS foreign key (id_type_wares_parent)
      references type_wares (id_type_wares)
      on delete restrict on update restrict;

alter table vendor
   add constraint FK_VENDOR_REFERENCE_TERITORY foreign key (id_ter)
      references teritory (id_ter)
      on delete restrict on update restrict;

alter table wares
   add constraint FK_WARES_REFERENCE_TYPE_ASS foreign key (id_type_wares)
      references type_wares (id_type_wares)
      on delete restrict on update restrict;

alter table wares
   add constraint FK_WARES_REFERENCE_VENDOR foreign key (id_vendor)
      references vendor (id_vendor)
      on delete restrict on update restrict;

