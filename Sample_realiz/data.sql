SET search_path = realiz, pg_catalog;

--DROP VIEW v_terit_reg;
CREATE OR REPLACE VIEW v_terit_reg AS
SELECT t.id_ter, tl.id_ter as id_ter_level, char_length(tl.rub_terit)/2 as level_ter 
FROM teritory t JOIN teritory tl ON t.rub_terit LIKE rtrim(tl.rub_terit) || '%';

--DROP VIEW v_subdiv;
CREATE OR REPLACE VIEW v_subdiv AS
SELECT t.id_subdiv, tl.id_subdiv as id_subdiv_level, char_length(tl.rub_subdiv)/2 as level_subdiv 
FROM subdivision t JOIN subdivision tl ON t.rub_subdiv LIKE rtrim(tl.rub_subdiv) || '%';

--TRUNCATE TABLE teritory;
INSERT INTO teritory VALUES (0, 0, 'Неизвестно', 			1, '');
INSERT INTO teritory VALUES (1, 0, 'Россия', 				1, '01');
INSERT INTO teritory VALUES (2, 1, 'Центральный регион', 2, '0101');
INSERT INTO teritory VALUES (3, 2, 'Московская обл.', 	3, '010101');
INSERT INTO teritory VALUES (4, 2, 'Москва', 				4, '010102');
INSERT INTO teritory VALUES (5, 2, 'Ленинградская обл.', 3,	'010103');
INSERT INTO teritory VALUES (6, 5, 'Выборг', 				4, '01010301');
INSERT INTO teritory VALUES (7, 1, 'Южный регион', 		2, '0102');
INSERT INTO teritory VALUES (8, 7, 'Нижегородская обл.', 3, '010201');
INSERT INTO teritory VALUES (9, 8, 'Н. Новгород', 			4, '01020101');
INSERT INTO teritory VALUES (10, 0, 'Германия', 			1, '02');
INSERT INTO teritory VALUES (11, 0, 'Южная корея', 		1, '03');
INSERT INTO teritory VALUES (12, 0, 'Япония', 				1, '04');
INSERT INTO teritory VALUES (14, 3, 'Мытищи', 				4, '01010101');

--TRUNCATE TABLE type_subdiv;
INSERT INTO type_subdiv VALUES (0, 'Неизвестно');
INSERT INTO type_subdiv VALUES (1, 'Дирекция');
INSERT INTO type_subdiv VALUES (2, 'Отдел');
INSERT INTO type_subdiv VALUES (3, 'Секция');

--TRUNCATE TABLE store;
INSERT INTO store VALUES (0, 0, 'Неизвестно');
INSERT INTO store VALUES (1, 14, 'ТехноМощь');
INSERT INTO store VALUES (2, 6,  'ТехноВещь');
INSERT INTO store VALUES (3, 9,  'ТехноПиск');

--TRUNCATE TABLE type_wares;
INSERT INTO type_wares VALUES (0, 0, 'Неизвестно',				'');
INSERT INTO type_wares VALUES (1, 0, 'Бытовая техника',		'01');
INSERT INTO type_wares VALUES (2, 1, 'Крупная быт. техника','0101');
INSERT INTO type_wares VALUES (3, 2, 'Холодильники',			'010101');
INSERT INTO type_wares VALUES (4, 2, 'Телевизоры',				'010102');
INSERT INTO type_wares VALUES (5, 2, 'Стиральные машины',	'010103');
INSERT INTO type_wares VALUES (6, 1, 'Мелкая быт. техника',	'0102');
INSERT INTO type_wares VALUES (7, 6, 'Утюги',					'010201');
INSERT INTO type_wares VALUES (8, 6, 'Соковыжималки',			'010202');
INSERT INTO type_wares VALUES (9, 0, 'Компьютерная техника','02');
INSERT INTO type_wares VALUES (10, 9, 'Компьютеры',			'0201');
INSERT INTO type_wares VALUES (12, 9, 'Периферия',				'0202');
INSERT INTO type_wares VALUES (14, 12, 'Принтеры',				'020201');
INSERT INTO type_wares VALUES (15, 12, 'Сканеры',				'020202');

--TRUNCATE TABLE subdivision;
INSERT INTO subdivision VALUES (0, 0, 0, 'Неизвестно', 						0, 0, '');
INSERT INTO subdivision VALUES (9, 1, 0, 'Дирекция',							1, 0, '01');
INSERT INTO subdivision VALUES (10, 2, 0, 'Дирекция',							1, 0, '02');
INSERT INTO subdivision VALUES (11, 3, 0, 'Дирекция',							1, 0, '03');
INSERT INTO subdivision VALUES (1, 1, 9, 'Отдел бытовой техники',			2, 1, '0101');
INSERT INTO subdivision VALUES (2, 1, 9, 'Отдел компьютерной техники',	2, 9, '0102');
INSERT INTO subdivision VALUES (3, 1, 1, 'Секция крупной быт.техники',	3, 2, '010101');
INSERT INTO subdivision VALUES (4, 1, 1, 'Секция мелкой быт.техники',	3, 6, '010102');
INSERT INTO subdivision VALUES (5, 2, 10, 'Отдел бытовой техники',		2, 1, '0201');
INSERT INTO subdivision VALUES (6, 2, 10, 'Отдел компьютерной техники',	2, 9, '0202');
INSERT INTO subdivision VALUES (7, 3, 11, 'Отдел бытовой техники',		2, 1, '0301');
INSERT INTO subdivision VALUES (8, 3, 11, 'Отдел компьютерной техники',	2, 9, '0302');

INSERT INTO personal (id_subdiv,pr_sex,fio_pers,pr_rukovod,date_rozd)
SELECT s.id_subdiv,(1+(random())::int) as pr_sex, '' as fio_pers,
	2 as pr_rukovod,'1960-01-01'::date + (random()*15*365)::int as date_rozd
	FROM subdivision s WHERE id_subdiv != 0
UNION ALL
SELECT s.id_subdiv,(1+(random())::int) as pr_sex, '' as fio_pers,
	1 as pr_rukovod,'1970-01-01'::date + (random()*5*365)::int as date_rozd
	FROM subdivision s WHERE id_subdiv IN (
		SELECT DISTINCT id_subdiv_parent FROM subdivision WHERE id_subdiv_parent != 0
	)
UNION ALL
SELECT s.id_subdiv,(1+(random())::int) as pr_sex, '' as fio_pers,
	0 as pr_rukovod,'1970-01-01'::date + (random()*5*365)::int as date_rozd
	FROM subdivision s JOIN ( 
		SELECT i_sub FROM (
			SELECT id_subdiv as i_sub, (4+random()*3)::int as l FROM subdivision sub
			) l CROSS JOIN generate_series(1, 7) s
		WHERE s <= l
		) ser ON s.id_subdiv = i_sub
	WHERE id_subdiv NOT IN (
		SELECT DISTINCT id_subdiv_parent FROM subdivision WHERE id_subdiv != 0
	);
UPDATE personal SET fio_pers = 'Persona' || id_pers || ' P.';
--SELECT * FROM personal;

INSERT INTO vendor VALUES (1, 10, 'Bosh');
INSERT INTO vendor VALUES (2, 10, 'Rowenta');
INSERT INTO vendor VALUES (3, 10, 'AEG');
INSERT INTO vendor VALUES (4, 11, 'Samsung');
INSERT INTO vendor VALUES (5, 11, 'LG');
INSERT INTO vendor VALUES (6, 11, 'Daewoo');
INSERT INTO vendor VALUES (7, 12, 'Sony');
INSERT INTO vendor VALUES (8, 12, 'Panasonic');

INSERT INTO wares (id_vendor,id_type_wares,price_wares,naim_wares)
SELECT v.id_vendor,w.id_type_wares,(20+(random()*150))::int*100 as price_wares,'' as naim_wares
	FROM vendor v CROSS JOIN type_wares w
	JOIN ( 
		SELECT i_sub FROM (
			SELECT id_type_wares as i_sub, (2+random()*4)::int as l FROM type_wares sub
			) l CROSS JOIN generate_series(1, 30) s
		WHERE s <= l
		) ser ON w.id_type_wares = i_sub
	WHERE id_type_wares NOT IN (
		SELECT DISTINCT id_type_wares_parent FROM type_wares
	)
	;

INSERT INTO realizations (id_pers,id_wares,date_realiz,num_realiz,sum_realiz,pc_discount,price)
SELECT p.id_pers,w.id_wares,'2007-01-01'::date + (random()*364)::int as date_realiz,
(1+(random()-0.42)::int) as num_realiz, 0 as sum_realiz,
CASE (random()*20)::int 
WHEN 1 THEN 5 WHEN 2 THEN 5 WHEN 3 THEN 5 WHEN 4 THEN 5 WHEN 5 THEN 5
WHEN 6 THEN 10 WHEN 7 THEN 10
WHEN 8 THEN 20
ELSE 0 END as pc_discount, price_wares
	FROM personal p NATURAL JOIN subdivision s CROSS JOIN wares w  
	JOIN ( 
		SELECT i_sub FROM (
			SELECT id_wares as i_sub, (10+random()*15)::int as l FROM wares sub
			) l CROSS JOIN generate_series(1, 30) s
		WHERE s <= l
		) ser ON w.id_wares = i_sub
	JOIN type_wares tw ON w.id_type_wares=tw.id_type_wares JOIN type_wares tw_up ON tw.rub_type_wares LIKE rtrim(tw_up.rub_type_wares)||'%'
	WHERE pr_rukovod = 0 AND s.id_type_wares = tw_up.id_type_wares
	;


--SELECT * FROM personal p NATURAL JOIN subdivision NATURAL JOIN type_wares w_up WHERE pr_rukovod = 0


UPDATE realizations SET sum_realiz = num_realiz * price * (100-pc_discount) / 100;

SELECT tu.id_ter, id_wares, date_part('quarter', r.date_realiz) as id_quarter
, 0 as num_deliv, (sum(sum_realiz)*(1.1 + random()/3))::int4 as sum_deliv
INTO realiz.delivery
	FROM realiz.realizations r
	JOIN realiz.personal ps USING(id_pers)
	JOIN realiz.subdivision sd USING(id_subdiv)
	JOIN realiz.store st USING(id_store)
	JOIN realiz.teritory tn USING(id_ter)
	JOIN realiz.teritory tu ON tn.rub_terit LIKE rtrim(tu.rub_terit)||'%'
	--CROSS JOIN (S(1.1 + random()/3)
	WHERE char_length(rtrim(tu.rub_terit)) = 6
	GROUP BY 1,2,3


--typ, id, id_parent, naim, id_gen, to_level
--TRUNCATE TABLE subdivision;
INSERT INTO realiz.support VALUES ('', 0, 0, 'Нет', 										0, '', '');
INSERT INTO realiz.support VALUES ('R', 1, 0, 'Территория продаж',					4, 'id_teritory', '2-4');
INSERT INTO realiz.support VALUES ('R', 2, 1, 'регионы',		 							4, 'id_teritory', '2');
INSERT INTO realiz.support VALUES ('R', 3, 1, 'регионы и области',					4, 'id_teritory', '2,3');
INSERT INTO realiz.support VALUES ('R', 4, 1, 'только области',						4, 'id_teritory', '3');
INSERT INTO realiz.support VALUES ('R', 5, 0, 'Кварталы',								1, 'id_quarter', '');
INSERT INTO realiz.support VALUES ('R', 6, 0, 'Месяца',									2, 'id_month', '');
INSERT INTO realiz.support VALUES ('R', 7, 0, 'Магазины',								3, 'id_store', '');
INSERT INTO realiz.support VALUES ('R', 8, 7, 'отделы',									5, 'id_subdiv', '2');
INSERT INTO realiz.support VALUES ('R', 9, 7, 'секции',									5, 'id_subdiv', '');
INSERT INTO realiz.support VALUES ('R', 10, 0, 'Вид товара',							7, 'id_type_wares', '');
INSERT INTO realiz.support VALUES ('R', 11, 10, '1й уровень',							7, 'id_type_wares', '1');
INSERT INTO realiz.support VALUES ('R', 12, 10, '1-2й уровни',							7, 'id_type_wares', '1-2');
INSERT INTO realiz.support VALUES ('R', 14, 10, '1-3й уровни',							7, 'id_type_wares', '1-3');
INSERT INTO realiz.support VALUES ('R', 15, 0, 'Фирмы',									8, 'id_vendor', '');
--INSERT INTO realiz.support VALUES ('R', 16, 0, 'Страны-экспортеры',					9, '');
INSERT INTO realiz.support VALUES ('R', 41, 0, 'Товар',									10, 'id_wares', '');
INSERT INTO realiz.support VALUES ('R', 42, 0, 'Продавец',								11, 'id_pers', '');
INSERT INTO realiz.support VALUES ('С', 18, 0, 'Кварталы',								1, 'id_quarter', '');
INSERT INTO realiz.support VALUES ('С', 19, 0, 'Месяца',									2, 'id_month', '');
INSERT INTO realiz.support VALUES ('С', 20, 0, 'Магазины',								3, 'id_store', '');
INSERT INTO realiz.support VALUES ('С', 21, 20, 'отделы',								5, 'id_subdiv', '');
INSERT INTO realiz.support VALUES ('С', 22, 0, 'Вид товара',		 					7, 'id_type_wares', '1');
INSERT INTO realiz.support VALUES ('С', 23, 0, 'Тип товара',							7, 'id_type_wares', '2');
INSERT INTO realiz.support VALUES ('С', 24, 0, 'Фирмы',									8, 'id_vendor', '');
--INSERT INTO realiz.support VALUES ('С', 25, 0, 'Страны-экспортеры',					9, '');
INSERT INTO realiz.support VALUES ('C', 26, 0, 'Регионы',		 						4, 'id_teritory', '2');

*}

