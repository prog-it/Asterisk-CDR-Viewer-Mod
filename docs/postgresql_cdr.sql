CREATE TABLE IF NOT EXISTS cdr (
    id serial8 NOT NULL,
    calldate timestamp NOT NULL DEFAULT '1970-01-01 00:00:00',
    clid varchar(80) NOT NULL DEFAULT '',
    src varchar(80) NOT NULL DEFAULT '',
    dst varchar(80) NOT NULL DEFAULT '',
    realdst varchar(80) NOT NULL DEFAULT '',
    dcontext varchar(80) NOT NULL DEFAULT '',
    channel varchar(80) NOT NULL DEFAULT '',
    dstchannel varchar(80) NOT NULL DEFAULT '',
    lastapp varchar(80) NOT NULL DEFAULT '',
    lastdata varchar(80) NOT NULL DEFAULT '',
    start timestamp NOT NULL DEFAULT '1970-01-01 00:00:00',
    answer timestamp NOT NULL DEFAULT '1970-01-01 00:00:00',
    stop timestamp NOT NULL DEFAULT '1970-01-01 00:00:00',
    duration int NOT NULL DEFAULT '0',
    billsec int NOT NULL DEFAULT '0',
    disposition varchar(45) NOT NULL DEFAULT '',
    amaflags int NOT NULL DEFAULT '0',
    remoteip varchar(60) NOT NULL DEFAULT '',
    accountcode varchar(20) NOT NULL DEFAULT '',
    peeraccount varchar(20) NOT NULL DEFAULT '',
    uniqueid varchar(32) NOT NULL DEFAULT '',
    userfield varchar(255) NOT NULL DEFAULT '',
    did varchar(50) NOT NULL DEFAULT '',
    linkedid varchar(32) NOT NULL DEFAULT '',
    sequence int NOT NULL DEFAULT '0',  
    filename varchar(255) DEFAULT 'none',
    CONSTRAINT cdr_pk PRIMARY KEY (id)
);

CREATE INDEX calldate ON cdr(calldate);
CREATE INDEX src ON cdr(src);
CREATE INDEX dst ON cdr(dst);
CREATE INDEX accountcode ON cdr(accountcode);
CREATE INDEX uniqueid ON cdr(uniqueid);
CREATE INDEX dcontext ON cdr(dcontext);
CREATE INDEX clid ON cdr(clid);
CREATE INDEX did ON cdr(did);

CREATE OR REPLACE FUNCTION trigger_cdr()
    RETURNS trigger
    LANGUAGE plpgsql
AS $function$  
begin
    if ((NEW.dst = 's' OR NEW.dst = '~~s~~') AND NEW.realdst != '') then
        NEW.dst = NEW.realdst;
    end if;
    return NEW;
end;
$function$
;

create trigger trigger_before before
insert
    on
    cdr for each row execute function trigger_cdr();
