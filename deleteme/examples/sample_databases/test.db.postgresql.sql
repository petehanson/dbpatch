--
-- PostgreSQL database dump
--

SET client_encoding = 'UTF8';
SET standard_conforming_strings = off;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET escape_string_warning = off;

SET search_path = public, pg_catalog;

--
-- Name: statinfo; Type: TYPE; Schema: public; Owner: peteh
--

CREATE TYPE statinfo AS (
	word text,
	ndoc integer,
	nentry integer
);


ALTER TYPE public.statinfo OWNER TO peteh;

--
-- Name: tokenout; Type: TYPE; Schema: public; Owner: peteh
--

CREATE TYPE tokenout AS (
	tokid integer,
	token text
);


ALTER TYPE public.tokenout OWNER TO peteh;

--
-- Name: tokentype; Type: TYPE; Schema: public; Owner: peteh
--

CREATE TYPE tokentype AS (
	tokid integer,
	alias text,
	descr text
);


ALTER TYPE public.tokentype OWNER TO peteh;

--
-- Name: tsdebug; Type: TYPE; Schema: public; Owner: peteh
--

CREATE TYPE tsdebug AS (
	ts_name text,
	tok_type text,
	description text,
	token text,
	dict_name text[],
	tsvector pg_catalog.tsvector
);


ALTER TYPE public.tsdebug OWNER TO peteh;

--
-- Name: gtsq; Type: DOMAIN; Schema: public; Owner: peteh
--

CREATE DOMAIN gtsq AS text;


ALTER DOMAIN public.gtsq OWNER TO peteh;

--
-- Name: gtsvector; Type: DOMAIN; Schema: public; Owner: peteh
--

CREATE DOMAIN gtsvector AS pg_catalog.gtsvector;


ALTER DOMAIN public.gtsvector OWNER TO peteh;

--
-- Name: tsquery; Type: DOMAIN; Schema: public; Owner: peteh
--

CREATE DOMAIN tsquery AS pg_catalog.tsquery;


ALTER DOMAIN public.tsquery OWNER TO peteh;

--
-- Name: tsvector; Type: DOMAIN; Schema: public; Owner: peteh
--

CREATE DOMAIN tsvector AS pg_catalog.tsvector;


ALTER DOMAIN public.tsvector OWNER TO peteh;

--
-- Name: _get_parser_from_curcfg(); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION _get_parser_from_curcfg() RETURNS text
    AS $$select prsname::text from pg_catalog.pg_ts_parser p join pg_ts_config c on cfgparser = p.oid where c.oid = show_curcfg();$$
    LANGUAGE sql IMMUTABLE STRICT;


ALTER FUNCTION public._get_parser_from_curcfg() OWNER TO peteh;

--
-- Name: concat(pg_catalog.tsvector, pg_catalog.tsvector); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION concat(pg_catalog.tsvector, pg_catalog.tsvector) RETURNS pg_catalog.tsvector
    AS $$tsvector_concat$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.concat(pg_catalog.tsvector, pg_catalog.tsvector) OWNER TO peteh;

--
-- Name: dex_init(internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION dex_init(internal) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_dex_init'
    LANGUAGE c;


ALTER FUNCTION public.dex_init(internal) OWNER TO peteh;

--
-- Name: dex_lexize(internal, internal, integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION dex_lexize(internal, internal, integer) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_dex_lexize'
    LANGUAGE c STRICT;


ALTER FUNCTION public.dex_lexize(internal, internal, integer) OWNER TO peteh;

--
-- Name: get_covers(pg_catalog.tsvector, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION get_covers(pg_catalog.tsvector, pg_catalog.tsquery) RETURNS text
    AS '$libdir/tsearch2', 'tsa_get_covers'
    LANGUAGE c STRICT;


ALTER FUNCTION public.get_covers(pg_catalog.tsvector, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: headline(oid, text, pg_catalog.tsquery, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION headline(oid, text, pg_catalog.tsquery, text) RETURNS text
    AS $$ts_headline_byid_opt$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.headline(oid, text, pg_catalog.tsquery, text) OWNER TO peteh;

--
-- Name: headline(oid, text, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION headline(oid, text, pg_catalog.tsquery) RETURNS text
    AS $$ts_headline_byid$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.headline(oid, text, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: headline(text, text, pg_catalog.tsquery, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION headline(text, text, pg_catalog.tsquery, text) RETURNS text
    AS '$libdir/tsearch2', 'tsa_headline_byname'
    LANGUAGE c IMMUTABLE STRICT;


ALTER FUNCTION public.headline(text, text, pg_catalog.tsquery, text) OWNER TO peteh;

--
-- Name: headline(text, text, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION headline(text, text, pg_catalog.tsquery) RETURNS text
    AS '$libdir/tsearch2', 'tsa_headline_byname'
    LANGUAGE c IMMUTABLE STRICT;


ALTER FUNCTION public.headline(text, text, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: headline(text, pg_catalog.tsquery, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION headline(text, pg_catalog.tsquery, text) RETURNS text
    AS $$ts_headline_opt$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.headline(text, pg_catalog.tsquery, text) OWNER TO peteh;

--
-- Name: headline(text, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION headline(text, pg_catalog.tsquery) RETURNS text
    AS $$ts_headline$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.headline(text, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: length(pg_catalog.tsvector); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION length(pg_catalog.tsvector) RETURNS integer
    AS $$tsvector_length$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.length(pg_catalog.tsvector) OWNER TO peteh;

--
-- Name: lexize(oid, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION lexize(oid, text) RETURNS text[]
    AS $$ts_lexize$$
    LANGUAGE internal STRICT;


ALTER FUNCTION public.lexize(oid, text) OWNER TO peteh;

--
-- Name: lexize(text, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION lexize(text, text) RETURNS text[]
    AS '$libdir/tsearch2', 'tsa_lexize_byname'
    LANGUAGE c STRICT;


ALTER FUNCTION public.lexize(text, text) OWNER TO peteh;

--
-- Name: lexize(text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION lexize(text) RETURNS text[]
    AS '$libdir/tsearch2', 'tsa_lexize_bycurrent'
    LANGUAGE c STRICT;


ALTER FUNCTION public.lexize(text) OWNER TO peteh;

--
-- Name: numnode(pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION numnode(pg_catalog.tsquery) RETURNS integer
    AS $$tsquery_numnode$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.numnode(pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: parse(oid, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION parse(oid, text) RETURNS SETOF tokenout
    AS $$ts_parse_byid$$
    LANGUAGE internal STRICT;


ALTER FUNCTION public.parse(oid, text) OWNER TO peteh;

--
-- Name: parse(text, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION parse(text, text) RETURNS SETOF tokenout
    AS $$ts_parse_byname$$
    LANGUAGE internal STRICT;


ALTER FUNCTION public.parse(text, text) OWNER TO peteh;

--
-- Name: parse(text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION parse(text) RETURNS SETOF tokenout
    AS '$libdir/tsearch2', 'tsa_parse_current'
    LANGUAGE c STRICT;


ALTER FUNCTION public.parse(text) OWNER TO peteh;

--
-- Name: plainto_tsquery(oid, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION plainto_tsquery(oid, text) RETURNS pg_catalog.tsquery
    AS $$plainto_tsquery_byid$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.plainto_tsquery(oid, text) OWNER TO peteh;

--
-- Name: plainto_tsquery(text, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION plainto_tsquery(text, text) RETURNS pg_catalog.tsquery
    AS '$libdir/tsearch2', 'tsa_plainto_tsquery_name'
    LANGUAGE c IMMUTABLE STRICT;


ALTER FUNCTION public.plainto_tsquery(text, text) OWNER TO peteh;

--
-- Name: plainto_tsquery(text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION plainto_tsquery(text) RETURNS pg_catalog.tsquery
    AS $$plainto_tsquery$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.plainto_tsquery(text) OWNER TO peteh;

--
-- Name: prsd_end(internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION prsd_end(internal) RETURNS void
    AS '$libdir/tsearch2', 'tsa_prsd_end'
    LANGUAGE c;


ALTER FUNCTION public.prsd_end(internal) OWNER TO peteh;

--
-- Name: prsd_getlexeme(internal, internal, internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION prsd_getlexeme(internal, internal, internal) RETURNS integer
    AS '$libdir/tsearch2', 'tsa_prsd_getlexeme'
    LANGUAGE c;


ALTER FUNCTION public.prsd_getlexeme(internal, internal, internal) OWNER TO peteh;

--
-- Name: prsd_headline(internal, internal, internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION prsd_headline(internal, internal, internal) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_prsd_headline'
    LANGUAGE c;


ALTER FUNCTION public.prsd_headline(internal, internal, internal) OWNER TO peteh;

--
-- Name: prsd_lextype(internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION prsd_lextype(internal) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_prsd_lextype'
    LANGUAGE c;


ALTER FUNCTION public.prsd_lextype(internal) OWNER TO peteh;

--
-- Name: prsd_start(internal, integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION prsd_start(internal, integer) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_prsd_start'
    LANGUAGE c;


ALTER FUNCTION public.prsd_start(internal, integer) OWNER TO peteh;

--
-- Name: querytree(pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION querytree(pg_catalog.tsquery) RETURNS text
    AS $$tsquerytree$$
    LANGUAGE internal STRICT;


ALTER FUNCTION public.querytree(pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: rank(real[], pg_catalog.tsvector, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION rank(real[], pg_catalog.tsvector, pg_catalog.tsquery) RETURNS real
    AS $$ts_rank_wtt$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.rank(real[], pg_catalog.tsvector, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: rank(real[], pg_catalog.tsvector, pg_catalog.tsquery, integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION rank(real[], pg_catalog.tsvector, pg_catalog.tsquery, integer) RETURNS real
    AS $$ts_rank_wttf$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.rank(real[], pg_catalog.tsvector, pg_catalog.tsquery, integer) OWNER TO peteh;

--
-- Name: rank(pg_catalog.tsvector, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION rank(pg_catalog.tsvector, pg_catalog.tsquery) RETURNS real
    AS $$ts_rank_tt$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.rank(pg_catalog.tsvector, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: rank(pg_catalog.tsvector, pg_catalog.tsquery, integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION rank(pg_catalog.tsvector, pg_catalog.tsquery, integer) RETURNS real
    AS $$ts_rank_ttf$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.rank(pg_catalog.tsvector, pg_catalog.tsquery, integer) OWNER TO peteh;

--
-- Name: rank_cd(real[], pg_catalog.tsvector, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION rank_cd(real[], pg_catalog.tsvector, pg_catalog.tsquery) RETURNS real
    AS $$ts_rankcd_wtt$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.rank_cd(real[], pg_catalog.tsvector, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: rank_cd(real[], pg_catalog.tsvector, pg_catalog.tsquery, integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION rank_cd(real[], pg_catalog.tsvector, pg_catalog.tsquery, integer) RETURNS real
    AS $$ts_rankcd_wttf$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.rank_cd(real[], pg_catalog.tsvector, pg_catalog.tsquery, integer) OWNER TO peteh;

--
-- Name: rank_cd(pg_catalog.tsvector, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION rank_cd(pg_catalog.tsvector, pg_catalog.tsquery) RETURNS real
    AS $$ts_rankcd_tt$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.rank_cd(pg_catalog.tsvector, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: rank_cd(pg_catalog.tsvector, pg_catalog.tsquery, integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION rank_cd(pg_catalog.tsvector, pg_catalog.tsquery, integer) RETURNS real
    AS $$ts_rankcd_ttf$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.rank_cd(pg_catalog.tsvector, pg_catalog.tsquery, integer) OWNER TO peteh;

--
-- Name: reset_tsearch(); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION reset_tsearch() RETURNS void
    AS '$libdir/tsearch2', 'tsa_reset_tsearch'
    LANGUAGE c STRICT;


ALTER FUNCTION public.reset_tsearch() OWNER TO peteh;

--
-- Name: rewrite(pg_catalog.tsquery, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION rewrite(pg_catalog.tsquery, text) RETURNS pg_catalog.tsquery
    AS $$tsquery_rewrite_query$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.rewrite(pg_catalog.tsquery, text) OWNER TO peteh;

--
-- Name: rewrite(pg_catalog.tsquery, pg_catalog.tsquery, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION rewrite(pg_catalog.tsquery, pg_catalog.tsquery, pg_catalog.tsquery) RETURNS pg_catalog.tsquery
    AS $$tsquery_rewrite$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.rewrite(pg_catalog.tsquery, pg_catalog.tsquery, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: rewrite_accum(pg_catalog.tsquery, pg_catalog.tsquery[]); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION rewrite_accum(pg_catalog.tsquery, pg_catalog.tsquery[]) RETURNS pg_catalog.tsquery
    AS '$libdir/tsearch2', 'tsa_rewrite_accum'
    LANGUAGE c;


ALTER FUNCTION public.rewrite_accum(pg_catalog.tsquery, pg_catalog.tsquery[]) OWNER TO peteh;

--
-- Name: rewrite_finish(pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION rewrite_finish(pg_catalog.tsquery) RETURNS pg_catalog.tsquery
    AS '$libdir/tsearch2', 'tsa_rewrite_finish'
    LANGUAGE c;


ALTER FUNCTION public.rewrite_finish(pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: set_curcfg(integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION set_curcfg(integer) RETURNS void
    AS '$libdir/tsearch2', 'tsa_set_curcfg'
    LANGUAGE c STRICT;


ALTER FUNCTION public.set_curcfg(integer) OWNER TO peteh;

--
-- Name: set_curcfg(text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION set_curcfg(text) RETURNS void
    AS '$libdir/tsearch2', 'tsa_set_curcfg_byname'
    LANGUAGE c STRICT;


ALTER FUNCTION public.set_curcfg(text) OWNER TO peteh;

--
-- Name: set_curdict(integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION set_curdict(integer) RETURNS void
    AS '$libdir/tsearch2', 'tsa_set_curdict'
    LANGUAGE c STRICT;


ALTER FUNCTION public.set_curdict(integer) OWNER TO peteh;

--
-- Name: set_curdict(text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION set_curdict(text) RETURNS void
    AS '$libdir/tsearch2', 'tsa_set_curdict_byname'
    LANGUAGE c STRICT;


ALTER FUNCTION public.set_curdict(text) OWNER TO peteh;

--
-- Name: set_curprs(integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION set_curprs(integer) RETURNS void
    AS '$libdir/tsearch2', 'tsa_set_curprs'
    LANGUAGE c STRICT;


ALTER FUNCTION public.set_curprs(integer) OWNER TO peteh;

--
-- Name: set_curprs(text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION set_curprs(text) RETURNS void
    AS '$libdir/tsearch2', 'tsa_set_curprs_byname'
    LANGUAGE c STRICT;


ALTER FUNCTION public.set_curprs(text) OWNER TO peteh;

--
-- Name: setweight(pg_catalog.tsvector, "char"); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION setweight(pg_catalog.tsvector, "char") RETURNS pg_catalog.tsvector
    AS $$tsvector_setweight$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.setweight(pg_catalog.tsvector, "char") OWNER TO peteh;

--
-- Name: show_curcfg(); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION show_curcfg() RETURNS oid
    AS $$get_current_ts_config$$
    LANGUAGE internal STABLE STRICT;


ALTER FUNCTION public.show_curcfg() OWNER TO peteh;

--
-- Name: snb_en_init(internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION snb_en_init(internal) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_snb_en_init'
    LANGUAGE c;


ALTER FUNCTION public.snb_en_init(internal) OWNER TO peteh;

--
-- Name: snb_lexize(internal, internal, integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION snb_lexize(internal, internal, integer) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_snb_lexize'
    LANGUAGE c STRICT;


ALTER FUNCTION public.snb_lexize(internal, internal, integer) OWNER TO peteh;

--
-- Name: snb_ru_init(internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION snb_ru_init(internal) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_snb_ru_init'
    LANGUAGE c;


ALTER FUNCTION public.snb_ru_init(internal) OWNER TO peteh;

--
-- Name: snb_ru_init_koi8(internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION snb_ru_init_koi8(internal) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_snb_ru_init_koi8'
    LANGUAGE c;


ALTER FUNCTION public.snb_ru_init_koi8(internal) OWNER TO peteh;

--
-- Name: snb_ru_init_utf8(internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION snb_ru_init_utf8(internal) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_snb_ru_init_utf8'
    LANGUAGE c;


ALTER FUNCTION public.snb_ru_init_utf8(internal) OWNER TO peteh;

--
-- Name: spell_init(internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION spell_init(internal) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_spell_init'
    LANGUAGE c;


ALTER FUNCTION public.spell_init(internal) OWNER TO peteh;

--
-- Name: spell_lexize(internal, internal, integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION spell_lexize(internal, internal, integer) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_spell_lexize'
    LANGUAGE c STRICT;


ALTER FUNCTION public.spell_lexize(internal, internal, integer) OWNER TO peteh;

--
-- Name: stat(text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION stat(text) RETURNS SETOF statinfo
    AS $$ts_stat1$$
    LANGUAGE internal STRICT;


ALTER FUNCTION public.stat(text) OWNER TO peteh;

--
-- Name: stat(text, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION stat(text, text) RETURNS SETOF statinfo
    AS $$ts_stat2$$
    LANGUAGE internal STRICT;


ALTER FUNCTION public.stat(text, text) OWNER TO peteh;

--
-- Name: strip(pg_catalog.tsvector); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION strip(pg_catalog.tsvector) RETURNS pg_catalog.tsvector
    AS $$tsvector_strip$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.strip(pg_catalog.tsvector) OWNER TO peteh;

--
-- Name: syn_init(internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION syn_init(internal) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_syn_init'
    LANGUAGE c;


ALTER FUNCTION public.syn_init(internal) OWNER TO peteh;

--
-- Name: syn_lexize(internal, internal, integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION syn_lexize(internal, internal, integer) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_syn_lexize'
    LANGUAGE c STRICT;


ALTER FUNCTION public.syn_lexize(internal, internal, integer) OWNER TO peteh;

--
-- Name: thesaurus_init(internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION thesaurus_init(internal) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_thesaurus_init'
    LANGUAGE c;


ALTER FUNCTION public.thesaurus_init(internal) OWNER TO peteh;

--
-- Name: thesaurus_lexize(internal, internal, integer, internal); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION thesaurus_lexize(internal, internal, integer, internal) RETURNS internal
    AS '$libdir/tsearch2', 'tsa_thesaurus_lexize'
    LANGUAGE c STRICT;


ALTER FUNCTION public.thesaurus_lexize(internal, internal, integer, internal) OWNER TO peteh;

--
-- Name: to_tsquery(oid, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION to_tsquery(oid, text) RETURNS pg_catalog.tsquery
    AS $$to_tsquery_byid$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.to_tsquery(oid, text) OWNER TO peteh;

--
-- Name: to_tsquery(text, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION to_tsquery(text, text) RETURNS pg_catalog.tsquery
    AS '$libdir/tsearch2', 'tsa_to_tsquery_name'
    LANGUAGE c IMMUTABLE STRICT;


ALTER FUNCTION public.to_tsquery(text, text) OWNER TO peteh;

--
-- Name: to_tsquery(text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION to_tsquery(text) RETURNS pg_catalog.tsquery
    AS $$to_tsquery$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.to_tsquery(text) OWNER TO peteh;

--
-- Name: to_tsvector(oid, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION to_tsvector(oid, text) RETURNS pg_catalog.tsvector
    AS $$to_tsvector_byid$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.to_tsvector(oid, text) OWNER TO peteh;

--
-- Name: to_tsvector(text, text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION to_tsvector(text, text) RETURNS pg_catalog.tsvector
    AS '$libdir/tsearch2', 'tsa_to_tsvector_name'
    LANGUAGE c IMMUTABLE STRICT;


ALTER FUNCTION public.to_tsvector(text, text) OWNER TO peteh;

--
-- Name: to_tsvector(text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION to_tsvector(text) RETURNS pg_catalog.tsvector
    AS $$to_tsvector$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.to_tsvector(text) OWNER TO peteh;

--
-- Name: token_type(integer); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION token_type(integer) RETURNS SETOF tokentype
    AS $$ts_token_type_byid$$
    LANGUAGE internal STRICT ROWS 16;


ALTER FUNCTION public.token_type(integer) OWNER TO peteh;

--
-- Name: token_type(text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION token_type(text) RETURNS SETOF tokentype
    AS $$ts_token_type_byname$$
    LANGUAGE internal STRICT ROWS 16;


ALTER FUNCTION public.token_type(text) OWNER TO peteh;

--
-- Name: token_type(); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION token_type() RETURNS SETOF tokentype
    AS '$libdir/tsearch2', 'tsa_token_type_current'
    LANGUAGE c STRICT ROWS 16;


ALTER FUNCTION public.token_type() OWNER TO peteh;

--
-- Name: ts_debug(text); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION ts_debug(text) RETURNS SETOF tsdebug
    AS $_$
select
        (select c.cfgname::text from pg_catalog.pg_ts_config as c
         where c.oid = show_curcfg()),
        t.alias as tok_type,
        t.descr as description,
        p.token,
        ARRAY ( SELECT m.mapdict::pg_catalog.regdictionary::pg_catalog.text
                FROM pg_catalog.pg_ts_config_map AS m
                WHERE m.mapcfg = show_curcfg() AND m.maptokentype = p.tokid
                ORDER BY m.mapseqno )
        AS dict_name,
        strip(to_tsvector(p.token)) as tsvector
from
        parse( _get_parser_from_curcfg(), $1 ) as p,
        token_type() as t
where
        t.tokid = p.tokid
$_$
    LANGUAGE sql STRICT;


ALTER FUNCTION public.ts_debug(text) OWNER TO peteh;

--
-- Name: tsearch2(); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION tsearch2() RETURNS trigger
    AS '$libdir/tsearch2', 'tsa_tsearch2'
    LANGUAGE c;


ALTER FUNCTION public.tsearch2() OWNER TO peteh;

--
-- Name: tsq_mcontained(pg_catalog.tsquery, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION tsq_mcontained(pg_catalog.tsquery, pg_catalog.tsquery) RETURNS boolean
    AS $$tsq_mcontained$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.tsq_mcontained(pg_catalog.tsquery, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: tsq_mcontains(pg_catalog.tsquery, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION tsq_mcontains(pg_catalog.tsquery, pg_catalog.tsquery) RETURNS boolean
    AS $$tsq_mcontains$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.tsq_mcontains(pg_catalog.tsquery, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: tsquery_and(pg_catalog.tsquery, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION tsquery_and(pg_catalog.tsquery, pg_catalog.tsquery) RETURNS pg_catalog.tsquery
    AS $$tsquery_and$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.tsquery_and(pg_catalog.tsquery, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: tsquery_not(pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION tsquery_not(pg_catalog.tsquery) RETURNS pg_catalog.tsquery
    AS $$tsquery_not$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.tsquery_not(pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: tsquery_or(pg_catalog.tsquery, pg_catalog.tsquery); Type: FUNCTION; Schema: public; Owner: peteh
--

CREATE FUNCTION tsquery_or(pg_catalog.tsquery, pg_catalog.tsquery) RETURNS pg_catalog.tsquery
    AS $$tsquery_or$$
    LANGUAGE internal IMMUTABLE STRICT;


ALTER FUNCTION public.tsquery_or(pg_catalog.tsquery, pg_catalog.tsquery) OWNER TO peteh;

--
-- Name: rewrite(pg_catalog.tsquery[]); Type: AGGREGATE; Schema: public; Owner: peteh
--

CREATE AGGREGATE rewrite(pg_catalog.tsquery[]) (
    SFUNC = rewrite_accum,
    STYPE = pg_catalog.tsquery,
    FINALFUNC = rewrite_finish
);


ALTER AGGREGATE public.rewrite(pg_catalog.tsquery[]) OWNER TO peteh;

--
-- Name: gin_tsvector_ops; Type: OPERATOR CLASS; Schema: public; Owner: peteh
--

CREATE OPERATOR CLASS gin_tsvector_ops
    FOR TYPE pg_catalog.tsvector USING gin AS
    STORAGE text ,
    OPERATOR 1 @@(pg_catalog.tsvector,pg_catalog.tsquery) ,
    OPERATOR 2 @@@(pg_catalog.tsvector,pg_catalog.tsquery) RECHECK ,
    FUNCTION 1 bttextcmp(text,text) ,
    FUNCTION 2 gin_extract_tsvector(pg_catalog.tsvector,internal) ,
    FUNCTION 3 gin_extract_tsquery(pg_catalog.tsquery,internal,smallint) ,
    FUNCTION 4 gin_tsquery_consistent(internal,smallint,pg_catalog.tsquery);


ALTER OPERATOR CLASS public.gin_tsvector_ops USING gin OWNER TO peteh;

--
-- Name: gist_tp_tsquery_ops; Type: OPERATOR CLASS; Schema: public; Owner: peteh
--

CREATE OPERATOR CLASS gist_tp_tsquery_ops
    FOR TYPE pg_catalog.tsquery USING gist AS
    STORAGE bigint ,
    OPERATOR 7 @>(pg_catalog.tsquery,pg_catalog.tsquery) RECHECK ,
    OPERATOR 8 <@(pg_catalog.tsquery,pg_catalog.tsquery) RECHECK ,
    FUNCTION 1 gtsquery_consistent(bigint,internal,integer) ,
    FUNCTION 2 gtsquery_union(internal,internal) ,
    FUNCTION 3 gtsquery_compress(internal) ,
    FUNCTION 4 gtsquery_decompress(internal) ,
    FUNCTION 5 gtsquery_penalty(internal,internal,internal) ,
    FUNCTION 6 gtsquery_picksplit(internal,internal) ,
    FUNCTION 7 gtsquery_same(bigint,bigint,internal);


ALTER OPERATOR CLASS public.gist_tp_tsquery_ops USING gist OWNER TO peteh;

--
-- Name: gist_tsvector_ops; Type: OPERATOR CLASS; Schema: public; Owner: peteh
--

CREATE OPERATOR CLASS gist_tsvector_ops
    FOR TYPE pg_catalog.tsvector USING gist AS
    STORAGE pg_catalog.gtsvector ,
    OPERATOR 1 @@(pg_catalog.tsvector,pg_catalog.tsquery) RECHECK ,
    FUNCTION 1 gtsvector_consistent(pg_catalog.gtsvector,internal,integer) ,
    FUNCTION 2 gtsvector_union(internal,internal) ,
    FUNCTION 3 gtsvector_compress(internal) ,
    FUNCTION 4 gtsvector_decompress(internal) ,
    FUNCTION 5 gtsvector_penalty(internal,internal,internal) ,
    FUNCTION 6 gtsvector_picksplit(internal,internal) ,
    FUNCTION 7 gtsvector_same(pg_catalog.gtsvector,pg_catalog.gtsvector,internal);


ALTER OPERATOR CLASS public.gist_tsvector_ops USING gist OWNER TO peteh;

--
-- Name: tsquery_ops; Type: OPERATOR CLASS; Schema: public; Owner: peteh
--

CREATE OPERATOR CLASS tsquery_ops
    FOR TYPE pg_catalog.tsquery USING btree AS
    OPERATOR 1 <(pg_catalog.tsquery,pg_catalog.tsquery) ,
    OPERATOR 2 <=(pg_catalog.tsquery,pg_catalog.tsquery) ,
    OPERATOR 3 =(pg_catalog.tsquery,pg_catalog.tsquery) ,
    OPERATOR 4 >=(pg_catalog.tsquery,pg_catalog.tsquery) ,
    OPERATOR 5 >(pg_catalog.tsquery,pg_catalog.tsquery) ,
    FUNCTION 1 tsquery_cmp(pg_catalog.tsquery,pg_catalog.tsquery);


ALTER OPERATOR CLASS public.tsquery_ops USING btree OWNER TO peteh;

--
-- Name: tsvector_ops; Type: OPERATOR CLASS; Schema: public; Owner: peteh
--

CREATE OPERATOR CLASS tsvector_ops
    FOR TYPE pg_catalog.tsvector USING btree AS
    OPERATOR 1 <(pg_catalog.tsvector,pg_catalog.tsvector) ,
    OPERATOR 2 <=(pg_catalog.tsvector,pg_catalog.tsvector) ,
    OPERATOR 3 =(pg_catalog.tsvector,pg_catalog.tsvector) ,
    OPERATOR 4 >=(pg_catalog.tsvector,pg_catalog.tsvector) ,
    OPERATOR 5 >(pg_catalog.tsvector,pg_catalog.tsvector) ,
    FUNCTION 1 tsvector_cmp(pg_catalog.tsvector,pg_catalog.tsvector);


ALTER OPERATOR CLASS public.tsvector_ops USING btree OWNER TO peteh;

--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

