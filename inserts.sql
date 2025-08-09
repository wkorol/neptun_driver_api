--
-- PostgreSQL database dump
--

-- Dumped from database version 17.5
-- Dumped by pg_dump version 17.5 (Debian 17.5-1.pgdg120+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: doctrine_migration_versions; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.doctrine_migration_versions (version, executed_at, execution_time) VALUES
	('DoctrineMigrations\Version20250505060102', '2025-05-05 08:02:14', 659),
	('DoctrineMigrations\Version20250505063800', '2025-05-05 08:38:30', 130),
	('DoctrineMigrations\Version20250705120327', '2025-07-05 16:49:42', 47);


--
-- Data for Name: lump_sums; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.lump_sums (id, name, fixed_values) VALUES
	('994ffc77-fbb8-4d91-a4c0-db783bbe23f8', 'Hotel Szydłowski', '[{"name": "Lotnisko", "tariff1": {"carValue": 55, "tariffType": 1, "bus5_6Value": 70, "bus7_8Value": null}, "tariff2": {"carValue": 65, "tariffType": 2, "bus5_6Value": 80, "bus7_8Value": null}}]'),
	('6543debe-11ff-44e2-918f-6170c6505c93', 'Sopot (Radisson Blu)', '[{"name":"Lotnisko","tariff1":{"tariffType":1,"carValue":110,"bus5_6Value":140,"bus7_8Value":160},"tariff2":{"tariffType":2,"carValue":130,"bus5_6Value":160,"bus7_8Value":170}},{"name":"Gda\u0144sk Centrum","tariff1":{"tariffType":1,"carValue":75,"bus5_6Value":95,"bus7_8Value":110},"tariff2":{"tariffType":2,"carValue":90,"bus5_6Value":105,"bus7_8Value":125}},{"name":"Sopot Centrum","tariff1":{"tariffType":1,"carValue":25,"bus5_6Value":40,"bus7_8Value":0},"tariff2":{"tariffType":2,"carValue":30,"bus5_6Value":50,"bus7_8Value":0}},{"name":"Gda\u0144sk Norwida 1","tariff1":{"tariffType":1,"carValue":45,"bus5_6Value":65,"bus7_8Value":0},"tariff2":{"tariffType":2,"carValue":55,"bus5_6Value":75,"bus7_8Value":0}}]'),
	('c099081f-f863-4a5f-a7c1-48b82c91a827', 'Hotel Orle', '[{"name": "Wyspa Sobieszewska, Przejazdowo", "tariff1": {"carValue": 60, "tariffType": 1, "bus5_6Value": 90, "bus7_8Value": null}, "tariff2": {"carValue": 75, "tariffType": 2, "bus5_6Value": 100, "bus7_8Value": null}}, {"name": "Lotnisko", "tariff1": {"carValue": 150, "tariffType": 1, "bus5_6Value": 220, "bus7_8Value": null}, "tariff2": {"carValue": 200, "tariffType": 2, "bus5_6Value": 260, "bus7_8Value": null}}, {"name": "Gdańsk Centrum, PKP, PKS", "tariff1": {"carValue": 100, "tariffType": 1, "bus5_6Value": 140, "bus7_8Value": null}, "tariff2": {"carValue": 125, "tariffType": 2, "bus5_6Value": 140, "bus7_8Value": null}}, {"name": "Oliwa Zoo / Park Oliwski", "tariff1": {"carValue": 135, "tariffType": 1, "bus5_6Value": 175, "bus7_8Value": null}, "tariff2": {"carValue": 155, "tariffType": 2, "bus5_6Value": 200, "bus7_8Value": null}}, {"name": "Z Cybulskiego 3 do Gdańsk Główny", "tariff1": {"carValue": 45, "tariffType": 1, "bus5_6Value": 65, "bus7_8Value": null}, "tariff2": {"carValue": 55, "tariffType": 2, "bus5_6Value": 75, "bus7_8Value": null}}, {"name": "Sopot", "tariff1": {"carValue": 150, "tariffType": 1, "bus5_6Value": 220, "bus7_8Value": null}, "tariff2": {"carValue": 150, "tariffType": 2, "bus5_6Value": 260, "bus7_8Value": null}}, {"name": "Gdynia Centum", "tariff1": {"carValue": 195, "tariffType": 1, "bus5_6Value": 290, "bus7_8Value": null}, "tariff2": {"carValue": 250, "tariffType": 2, "bus5_6Value": 340, "bus7_8Value": null}}]'),
	('dc55b346-c1c1-4a51-862f-27a2dfecab49', 'Focus Premium - Wrzeszcz', '[{"name": "Lotnisko", "tariff1": {"carValue": 65, "tariffType": 1, "bus5_6Value": 85, "bus7_8Value": null}, "tariff2": {"carValue": 75, "tariffType": 2, "bus5_6Value": 100, "bus7_8Value": null}}, {"name": "Brzeźno", "tariff1": {"carValue": 30, "tariffType": 1, "bus5_6Value": 45, "bus7_8Value": null}, "tariff2": {"carValue": 35, "tariffType": 2, "bus5_6Value": 55, "bus7_8Value": null}}, {"name": "Stadion", "tariff1": {"carValue": 35, "tariffType": 1, "bus5_6Value": 50, "bus7_8Value": null}, "tariff2": {"carValue": 40, "tariffType": 2, "bus5_6Value": 60, "bus7_8Value": null}}, {"name": "Gdańsk Centrum, Dworzec PKP/PKS", "tariff1": {"carValue": 35, "tariffType": 1, "bus5_6Value": 50, "bus7_8Value": null}, "tariff2": {"carValue": 40, "tariffType": 2, "bus5_6Value": 60, "bus7_8Value": null}}, {"name": "Focus Elbląska", "tariff1": {"carValue": 50, "tariffType": 1, "bus5_6Value": 70, "bus7_8Value": null}, "tariff2": {"carValue": 60, "tariffType": 2, "bus5_6Value": 80, "bus7_8Value": null}}, {"name": "Ergo Arena", "tariff1": {"carValue": 40, "tariffType": 1, "bus5_6Value": 55, "bus7_8Value": null}, "tariff2": {"carValue": 50, "tariffType": 2, "bus5_6Value": 65, "bus7_8Value": null}}, {"name": "Olivia Business Centre", "tariff1": {"carValue": 25, "tariffType": 1, "bus5_6Value": 35, "bus7_8Value": null}, "tariff2": {"carValue": 30, "tariffType": 2, "bus5_6Value": 40, "bus7_8Value": null}}, {"name": "Sopot", "tariff1": {"carValue": 55, "tariffType": 1, "bus5_6Value": 75, "bus7_8Value": null}, "tariff2": {"carValue": 70, "tariffType": 2, "bus5_6Value": 90, "bus7_8Value": null}}, {"name": "Gdynia Centrum", "tariff1": {"carValue": 90, "tariffType": 1, "bus5_6Value": 120, "bus7_8Value": null}, "tariff2": {"carValue": 110, "tariffType": 2, "bus5_6Value": 140, "bus7_8Value": null}}]'),
	('5fbf89f2-01b1-4746-98cb-7cd4a9c4a74b', 'Sol Marina', '[{"name": "Wyspa Sobieszewska, Przejazdowo", "tariff1": {"carValue": 50, "tariffType": 1, "bus5_6Value": 75, "bus7_8Value": null}, "tariff2": {"carValue": 50, "tariffType": 2, "bus5_6Value": 75, "bus7_8Value": null}}, {"name": "Gdańsk Centrum / Dworzec / PKS", "tariff1": {"carValue": 95, "tariffType": 1, "bus5_6Value": 115, "bus7_8Value": null}, "tariff2": {"carValue": 95, "tariffType": 2, "bus5_6Value": 115, "bus7_8Value": null}}, {"name": "Lotnisko", "tariff1": {"carValue": 150, "tariffType": 1, "bus5_6Value": 180, "bus7_8Value": null}, "tariff2": {"carValue": 150, "tariffType": 2, "bus5_6Value": 180, "bus7_8Value": null}}, {"name": "Gdańsk Oliwa - ZOO / Park Oliwski", "tariff1": {"carValue": 135, "tariffType": 1, "bus5_6Value": 170, "bus7_8Value": null}, "tariff2": {"carValue": 135, "tariffType": 2, "bus5_6Value": 170, "bus7_8Value": null}}, {"name": "Sopot", "tariff1": {"carValue": 150, "tariffType": 1, "bus5_6Value": 200, "bus7_8Value": null}, "tariff2": {"carValue": 150, "tariffType": 2, "bus5_6Value": 200, "bus7_8Value": null}}, {"name": "Gdynia Centrum", "tariff1": {"carValue": 200, "tariffType": 1, "bus5_6Value": 250, "bus7_8Value": null}, "tariff2": {"carValue": 200, "tariffType": 2, "bus5_6Value": 250, "bus7_8Value": null}}]'),
	('5a03ea81-d196-490c-bddc-3c2e3d77491e', 'Ryczalty Zimowe', '[{"name": "Lotnisko", "tariff1": {"carValue": 85, "tariffType": 1, "bus5_6Value": 115, "bus7_8Value": null}, "tariff2": {"carValue": 110, "tariffType": 2, "bus5_6Value": 135, "bus7_8Value": null}}, {"name": "Gdańsk Dworzec PKP/PKS ", "tariff1": {"carValue": 30, "tariffType": 1, "bus5_6Value": 40, "bus7_8Value": null}, "tariff2": {"carValue": 30, "tariffType": 2, "bus5_6Value": 50, "bus7_8Value": null}}, {"name": "Amber Expo - Żaglowa", "tariff1": {"carValue": 40, "tariffType": 1, "bus5_6Value": 55, "bus7_8Value": null}, "tariff2": {"carValue": 50, "tariffType": 2, "bus5_6Value": 65, "bus7_8Value": null}}, {"name": "Brzeźno", "tariff1": {"carValue": 50, "tariffType": 1, "bus5_6Value": 70, "bus7_8Value": null}, "tariff2": {"carValue": 60, "tariffType": 2, "bus5_6Value": 80, "bus7_8Value": null}}, {"name": "Galeria Bałtycka", "tariff1": {"carValue": 45, "tariffType": 1, "bus5_6Value": 60, "bus7_8Value": null}, "tariff2": {"carValue": 50, "tariffType": 2, "bus5_6Value": 70, "bus7_8Value": null}}, {"name": "Designer Outlet / Fashion House", "tariff1": {"carValue": 50, "tariffType": 1, "bus5_6Value": 70, "bus7_8Value": null}, "tariff2": {"carValue": 60, "tariffType": 2, "bus5_6Value": 80, "bus7_8Value": null}}, {"name": "Olivia Business Centre", "tariff1": {"carValue": 50, "tariffType": 1, "bus5_6Value": 70, "bus7_8Value": null}, "tariff2": {"carValue": 60, "tariffType": 2, "bus5_6Value": 80, "bus7_8Value": null}}, {"name": "Sopot", "tariff1": {"carValue": 70, "tariffType": 1, "bus5_6Value": 100, "bus7_8Value": null}, "tariff2": {"carValue": 90, "tariffType": 2, "bus5_6Value": 120, "bus7_8Value": null}}, {"name": "Gdynia Centrum", "tariff1": {"carValue": 120, "tariffType": 1, "bus5_6Value": 150, "bus7_8Value": null}, "tariff2": {"carValue": 140, "tariffType": 2, "bus5_6Value": 170, "bus7_8Value": null}}, {"name": "Gdynia - Stena Line", "tariff1": {"carValue": 140, "tariffType": 1, "bus5_6Value": 185, "bus7_8Value": null}, "tariff2": {"carValue": 160, "tariffType": 2, "bus5_6Value": 200, "bus7_8Value": null}}]'),
	('d74b38e2-8562-4d23-ba6f-95270500e640', 'Hotele Manego', '[{"name":"Lotnisko","tariff1":{"tariffType":1,"carValue":99,"bus5_6Value":125,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":99,"bus5_6Value":125,"bus7_8Value":null}},{"name":"Stogi Pla\u017ca","tariff1":{"tariffType":1,"carValue":40,"bus5_6Value":50,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":55,"bus5_6Value":65,"bus7_8Value":null}},{"name":"Westerplatte","tariff1":{"tariffType":1,"carValue":50,"bus5_6Value":75,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":70,"bus5_6Value":105,"bus7_8Value":null}},{"name":"Gda\u0144sk Centrum, Dworzec PKP\/PKS","tariff1":{"tariffType":1,"carValue":30,"bus5_6Value":40,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":30,"bus5_6Value":40,"bus7_8Value":null}},{"name":"Gda\u0144sk - Stocznia Remontowa (GSR)","tariff1":{"tariffType":1,"carValue":50,"bus5_6Value":70,"bus7_8Value":80},"tariff2":{"tariffType":2,"carValue":50,"bus5_6Value":70,"bus7_8Value":80}},{"name":"Galeria Ba\u0142tycka oraz Grunwaldzka 103a","tariff1":{"tariffType":1,"carValue":45,"bus5_6Value":65,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":45,"bus5_6Value":65,"bus7_8Value":null}},{"name":"Olivia Business Centre oraz Alchemia","tariff1":{"tariffType":1,"carValue":50,"bus5_6Value":75,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":50,"bus5_6Value":75,"bus7_8Value":null}},{"name":"Oliwa ZOO","tariff1":{"tariffType":1,"carValue":60,"bus5_6Value":90,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":70,"bus5_6Value":90,"bus7_8Value":null}},{"name":"Designer Outlet \/ Fashion House","tariff1":{"tariffType":1,"carValue":55,"bus5_6Value":65,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":70,"bus5_6Value":85,"bus7_8Value":null}},{"name":"Sopot","tariff1":{"tariffType":1,"carValue":70,"bus5_6Value":105,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":80,"bus5_6Value":120,"bus7_8Value":null}},{"name":"Gdynia","tariff1":{"tariffType":1,"carValue":120,"bus5_6Value":150,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":140,"bus5_6Value":180,"bus7_8Value":null}},{"name":"Gdynia - Riviera","tariff1":{"tariffType":1,"carValue":90,"bus5_6Value":120,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":110,"bus5_6Value":150,"bus7_8Value":null}},{"name":"Gdynia - Stena Line","tariff1":{"tariffType":1,"carValue":150,"bus5_6Value":220,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":180,"bus5_6Value":270,"bus7_8Value":null}}]'),
	('b5bc3fa3-7528-4a5b-9e03-761c829bb2dc', 'Hotel Amber', '[{"name":"Lotnisko","tariff1":{"tariffType":1,"carValue":75,"bus5_6Value":100,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":100,"bus5_6Value":120,"bus7_8Value":null}},{"name":"Gda\u0144sk Centrum, Dworzec PKP\/PKS","tariff1":{"tariffType":1,"carValue":35,"bus5_6Value":50,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":40,"bus5_6Value":60,"bus7_8Value":null}},{"name":"Akademia Medyczna","tariff1":{"tariffType":1,"carValue":25,"bus5_6Value":35,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":30,"bus5_6Value":50,"bus7_8Value":null}},{"name":"Galeria Ba\u0142tycka oraz Grunwaldzka 103a","tariff1":{"tariffType":1,"carValue":40,"bus5_6Value":48,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":50,"bus5_6Value":70,"bus7_8Value":null}},{"name":"Sopot","tariff1":{"tariffType":1,"carValue":80,"bus5_6Value":100,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":95,"bus5_6Value":115,"bus7_8Value":null}}]'),
	('8c957fb5-ea9e-43b0-beec-1e7224fd6744', 'Hampton By Hilton - Oliwa', '[{"name": "Lotnisko", "tariff1": {"carValue": 90, "tariffType": 1, "bus5_6Value": 125, "bus7_8Value": null}, "tariff2": {"carValue": 120, "tariffType": 2, "bus5_6Value": 145, "bus7_8Value": null}}, {"name": "Gdańsk Centrum - Dworzec PKP/PKS ", "tariff1": {"carValue": 60, "tariffType": 1, "bus5_6Value": 90, "bus7_8Value": null}, "tariff2": {"carValue": 85, "tariffType": 2, "bus5_6Value": 120, "bus7_8Value": null}}, {"name": "Galeria Bałtycka", "tariff1": {"carValue": 40, "tariffType": 1, "bus5_6Value": 65, "bus7_8Value": null}, "tariff2": {"carValue": 55, "tariffType": 2, "bus5_6Value": 80, "bus7_8Value": null}}, {"name": "Amber Expo", "tariff1": {"carValue": 50, "tariffType": 1, "bus5_6Value": 70, "bus7_8Value": null}, "tariff2": {"carValue": 65, "tariffType": 2, "bus5_6Value": 85, "bus7_8Value": null}}, {"name": "Olivia Business Centre", "tariff1": {"carValue": 30, "tariffType": 1, "bus5_6Value": 50, "bus7_8Value": null}, "tariff2": {"carValue": 40, "tariffType": 2, "bus5_6Value": 60, "bus7_8Value": null}}, {"name": "Sopot", "tariff1": {"carValue": 40, "tariffType": 1, "bus5_6Value": 65, "bus7_8Value": null}, "tariff2": {"carValue": 55, "tariffType": 2, "bus5_6Value": 80, "bus7_8Value": null}}, {"name": "Gdynia Centrum", "tariff1": {"carValue": 100, "tariffType": 1, "bus5_6Value": 140, "bus7_8Value": null}, "tariff2": {"carValue": 135, "tariffType": 2, "bus5_6Value": 170, "bus7_8Value": null}}]'),
	('b9a65faa-aebf-4ef5-a49d-9cff26f9d289', 'Ryczałty Sopot (Z ryczałtem po sopocie)', '[{"name":"Sopot","tariff1":{"tariffType":1,"carValue":30,"bus5_6Value":45,"bus7_8Value":55},"tariff2":{"tariffType":2,"carValue":30,"bus5_6Value":55,"bus7_8Value":55}},{"name":"Lotnisko","tariff1":{"tariffType":1,"carValue":110,"bus5_6Value":140,"bus7_8Value":160},"tariff2":{"tariffType":2,"carValue":130,"bus5_6Value":160,"bus7_8Value":170}},{"name":"Gda\u0144sk Centrum","tariff1":{"tariffType":1,"carValue":75,"bus5_6Value":95,"bus7_8Value":110},"tariff2":{"tariffType":2,"carValue":90,"bus5_6Value":105,"bus7_8Value":110}}]'),
	('aaefe68a-9eda-4bda-9982-91b1a7a17da9', 'Nordbyhus', '[{"name":"Lotnisko","tariff1":{"tariffType":1,"carValue":90,"bus5_6Value":120,"bus7_8Value":140},"tariff2":{"tariffType":2,"carValue":90,"bus5_6Value":120,"bus7_8Value":140}},{"name":"Dworzec PKP\/PKS","tariff1":{"tariffType":1,"carValue":30,"bus5_6Value":45,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":30,"bus5_6Value":55,"bus7_8Value":null}},{"name":"Galeria Ba\u0142tycka oraz Grunwaldzka 103a","tariff1":{"tariffType":1,"carValue":50,"bus5_6Value":70,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":60,"bus5_6Value":75,"bus7_8Value":null}},{"name":"Designer Outlet","tariff1":{"tariffType":1,"carValue":60,"bus5_6Value":80,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":70,"bus5_6Value":90,"bus7_8Value":null}},{"name":"Olivia Business Centre oraz Alchemia","tariff1":{"tariffType":1,"carValue":55,"bus5_6Value":70,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":65,"bus5_6Value":80,"bus7_8Value":null}},{"name":"Sopot","tariff1":{"tariffType":1,"carValue":70,"bus5_6Value":85,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":80,"bus5_6Value":95,"bus7_8Value":null}},{"name":"Gdynia Centrum","tariff1":{"tariffType":1,"carValue":110,"bus5_6Value":140,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":130,"bus5_6Value":150,"bus7_8Value":null}},{"name":"Gdynia - Stena Line","tariff1":{"tariffType":1,"carValue":150,"bus5_6Value":180,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":130,"bus5_6Value":190,"bus7_8Value":null}},{"name":"Amber Expo: Stadion (\u017baglowa)","tariff1":{"tariffType":1,"carValue":40,"bus5_6Value":55,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":50,"bus5_6Value":65,"bus7_8Value":null}}]'),
	('73600420-046b-47f4-b9fe-c8ff10dbec0f', 'OSW Dorota', '[{"name":"Gda\u0144sk G\u0142\u00f3wny","tariff1":{"tariffType":1,"carValue":150,"bus5_6Value":200,"bus7_8Value":240},"tariff2":{"tariffType":2,"carValue":175,"bus5_6Value":225,"bus7_8Value":250}},{"name":"Lotnisko","tariff1":{"tariffType":1,"carValue":160,"bus5_6Value":210,"bus7_8Value":240},"tariff2":{"tariffType":2,"carValue":185,"bus5_6Value":235,"bus7_8Value":260}},{"name":"Sopot","tariff1":{"tariffType":1,"carValue":200,"bus5_6Value":250,"bus7_8Value":280},"tariff2":{"tariffType":2,"carValue":220,"bus5_6Value":275,"bus7_8Value":290}},{"name":"Gdynia","tariff1":{"tariffType":1,"carValue":240,"bus5_6Value":270,"bus7_8Value":290},"tariff2":{"tariffType":2,"carValue":250,"bus5_6Value":280,"bus7_8Value":300}}]'),
	('8bee90f4-bb54-4a0e-b302-bce11e79ed82', 'Sopot (Focus)', '[{"name":"Sopot Centrum","tariff1":{"tariffType":1,"carValue":30,"bus5_6Value":45,"bus7_8Value":55},"tariff2":{"tariffType":2,"carValue":30,"bus5_6Value":55,"bus7_8Value":65}},{"name":"Lotnisko","tariff1":{"tariffType":1,"carValue":110,"bus5_6Value":140,"bus7_8Value":160},"tariff2":{"tariffType":2,"carValue":130,"bus5_6Value":160,"bus7_8Value":170}},{"name":"Gda\u0144sk Centrum","tariff1":{"tariffType":1,"carValue":75,"bus5_6Value":95,"bus7_8Value":110},"tariff2":{"tariffType":2,"carValue":90,"bus5_6Value":105,"bus7_8Value":125}},{"name":"Gdynia Riviera, PKP, Skwer Ko\u015bciuszki","tariff1":{"tariffType":1,"carValue":80,"bus5_6Value":110,"bus7_8Value":120},"tariff2":{"tariffType":2,"carValue":95,"bus5_6Value":125,"bus7_8Value":160}},{"name":"Gdynia - Stena Line","tariff1":{"tariffType":1,"carValue":100,"bus5_6Value":135,"bus7_8Value":145},"tariff2":{"tariffType":2,"carValue":125,"bus5_6Value":150,"bus7_8Value":160}}]'),
	('1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', 'Ryczałty letnie', '[{"name":"Lotnisko","tariff1":{"tariffType":1,"carValue":95,"bus5_6Value":125,"bus7_8Value":140},"tariff2":{"tariffType":2,"carValue":120,"bus5_6Value":145,"bus7_8Value":170}},{"name":"Gda\u0144sk Centrum \/ Dworzec PKP\/PKS ","tariff1":{"tariffType":1,"carValue":30,"bus5_6Value":40,"bus7_8Value":45},"tariff2":{"tariffType":2,"carValue":35,"bus5_6Value":50,"bus7_8Value":65}},{"name":"Amber Expo - \u017baglowa","tariff1":{"tariffType":1,"carValue":40,"bus5_6Value":60,"bus7_8Value":65},"tariff2":{"tariffType":2,"carValue":50,"bus5_6Value":70,"bus7_8Value":75}},{"name":"Gda\u0144sk - Stocznia Remontowa (GSR)","tariff1":{"tariffType":1,"carValue":50,"bus5_6Value":70,"bus7_8Value":80},"tariff2":{"tariffType":2,"carValue":50,"bus5_6Value":70,"bus7_8Value":80}},{"name":"Brze\u017ano","tariff1":{"tariffType":1,"carValue":50,"bus5_6Value":65,"bus7_8Value":75},"tariff2":{"tariffType":2,"carValue":60,"bus5_6Value":75,"bus7_8Value":90}},{"name":"Galeria Ba\u0142tycka oraz Grunwaldzka 103a","tariff1":{"tariffType":1,"carValue":50,"bus5_6Value":65,"bus7_8Value":75},"tariff2":{"tariffType":2,"carValue":60,"bus5_6Value":75,"bus7_8Value":85}},{"name":"Designer Outlet \/ Fashion House","tariff1":{"tariffType":1,"carValue":60,"bus5_6Value":85,"bus7_8Value":null},"tariff2":{"tariffType":2,"carValue":75,"bus5_6Value":100,"bus7_8Value":null}},{"name":"Olivia Business Centre oraz Alchemia","tariff1":{"tariffType":1,"carValue":60,"bus5_6Value":75,"bus7_8Value":85},"tariff2":{"tariffType":2,"carValue":70,"bus5_6Value":85,"bus7_8Value":95}},{"name":"Sopot","tariff1":{"tariffType":1,"carValue":75,"bus5_6Value":100,"bus7_8Value":110},"tariff2":{"tariffType":2,"carValue":90,"bus5_6Value":115,"bus7_8Value":130}}]');


--
-- Data for Name: region; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.region (id, name, "position") VALUES
	(11, 'Rejon 11 - Przejazdowo, Wyspa Sobieszewska', 1),
	(13, 'Rejon 13 - Centrum', 4),
	(24, 'Rejon 24 - Suchanino', 5),
	(31, 'Rejon 31 - Górny Wrzeszcz', 6),
	(33, 'Rejon 33 - Oliwa', 8),
	(36, 'Rejon 36 - Dolny Wrzeszcz', 7),
	(51, 'Rejon 51 - Sopot', 9),
	(122, 'Rejon 122 - Radisson Blu', 3),
	(121, 'Rejon 12 / 121 (Wyspa Spichrzów / Dolne Miasto)', 2),
	(421, 'Rejon 42 / 421 (Kowale / Designer Outlet)', 10);


--
-- Data for Name: hotel; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.hotel (id, region_id, lump_sums_id, new_lump_sums_id, name, lump_sums_expire_date, update_date) VALUES
	('01d34a22-4879-4cac-a356-5ece828acaa4', 11, 'c099081f-f863-4a5f-a7c1-48b82c91a827', NULL, 'Orle', NULL, '2024-11-17 17:45:36'),
	('28663962-bef8-4af7-a17c-f7bc3139e7a2', 51, 'b9a65faa-aebf-4ef5-a49d-9cff26f9d289', NULL, 'Marriott Sopot', NULL, '2024-11-17 18:13:01'),
	('288b0baf-7838-4849-b8ba-f27760aac543', 24, 'b5bc3fa3-7528-4a5b-9e03-761c829bb2dc', NULL, 'Amber', NULL, '2024-11-20 14:15:06'),
	('3ed8ecef-4a94-45ae-bfd9-c364fa65a5e2', 11, '5fbf89f2-01b1-4746-98cb-7cd4a9c4a74b', NULL, 'Sol Marina', NULL, '2024-11-11 22:28:47'),
	('4f4e032a-c6b9-4ef6-bc3a-9318d81b110f', 13, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Montownia', NULL, '2024-11-17 17:59:13'),
	('601134e8-2c33-4f83-9732-e27ef641cf0f', 13, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Central', NULL, '2024-11-17 17:58:56'),
	('62f748bd-9c10-40af-99a7-ea33ebe33798', 51, '6543debe-11ff-44e2-918f-6170c6505c93', NULL, 'Radisson Blu - Sopot', NULL, '2025-03-28 13:17:15'),
	('86181f6d-52e0-434a-b293-fa08d559aff2', 121, 'aaefe68a-9eda-4bda-9982-91b1a7a17da9', NULL, 'Apartamenty Nordbyhus', NULL, '2024-12-23 10:26:43'),
	('ab9ba742-b8a1-4a2c-88de-5ae935ce4881', 121, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Cloud One - Stągiewna 27', NULL, '2024-12-01 11:15:48'),
	('afa90b71-4144-491f-b54b-196154da7d9d', 13, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Hampton By Hilton', NULL, '2024-11-17 17:59:33'),
	('b4971a1a-edca-4271-b2e7-b74c7e414031', 31, '994ffc77-fbb8-4d91-a4c0-db783bbe23f8', NULL, 'Szydłowski', NULL, '2024-11-17 18:05:04'),
	('de766824-6759-4d25-8b4a-9c06f6efa3e0', 33, '8c957fb5-ea9e-43b0-beec-1e7224fd6744', NULL, 'Hampton By Hilton - Oliwa', NULL, '2024-11-28 12:05:42'),
	('f460c644-4390-4198-8640-cf9dcaaa9ba5', 121, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'PURO', NULL, '2024-11-17 17:52:03'),
	('f87c550c-8af4-43e1-987f-141570cbbe64', 36, 'dc55b346-c1c1-4a51-862f-27a2dfecab49', NULL, 'Focus Premium - Wrzeszcz', NULL, '2024-11-17 18:08:04'),
	('fff8b451-0769-4eee-9b31-14c315733bec', 36, 'dc55b346-c1c1-4a51-862f-27a2dfecab49', NULL, 'Boutique Residence - Kościuszki 8A', NULL, '2024-11-27 06:50:53'),
	('a3a4e696-ef44-4d30-988c-b1062cb7b455', 122, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Radisson Blu - Powróżnicza', NULL, '2024-11-11 22:35:52'),
	('90e80f23-f1cd-45c7-9420-152bc6492718', 121, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Radisson - Chmielna 10 / Radisson - Red Chmielna 2', NULL, '2025-01-25 15:08:21'),
	('f5828a07-1e39-4452-ab05-45d70d66f73b', 13, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Scandic', NULL, '2024-11-17 17:46:17'),
	('5f2407c2-3353-4bbb-9d93-933644fa6772', 121, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Novotel - Pszenna', NULL, '2024-11-11 22:35:28'),
	('2347740e-5157-4e0f-a1cc-cf1da0180887', 13, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Aparthotel Neptun', NULL, '2024-11-11 22:38:52'),
	('3f1c39a9-c1a3-4718-8cfb-d50da7922e30', 13, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Heweliusz (Mercure)', NULL, '2024-11-17 17:46:08'),
	('d5e53052-088b-466f-8987-5b7cc08cc017', 13, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Ibis', NULL, '2024-11-17 17:45:45'),
	('c03b07fe-6629-4f40-96d2-f51a3d3cedb5', 13, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Wolne Miasto', NULL, '2024-11-17 17:47:08'),
	('eb0c0117-9a81-445a-8919-3fafa051c18d', 121, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Holiday Inn', NULL, '2024-11-11 22:35:00'),
	('0fc905f6-bcf8-4e65-8782-910f06dfc48a', 13, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Q Hotel Grand Cru', NULL, '2024-11-11 22:38:41'),
	('3d3c7aad-7e36-45a1-bec0-2e735cc9fb73', 13, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Bonum', NULL, '2024-11-17 17:47:37'),
	('5cfde92f-1d3b-4506-aed4-f9b434c0379d', 121, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Kobza Haus', NULL, '2024-11-11 22:34:50'),
	('cd55a4a2-c224-4a46-b682-954232b13620', 121, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Arche Dwór Uphagena ', NULL, '2024-11-27 09:28:02'),
	('c6b5aac4-fce0-4023-adc0-fca4797ff4ab', 121, '1f9f28fb-1f3e-41ce-873d-bbbaf8a818d2', NULL, 'Focus Elbląska', NULL, '2024-11-11 22:34:34'),
	('3e9621b3-d480-493f-98c8-6aaba7ce8aa4', 121, 'd74b38e2-8562-4d23-ba6f-95270500e640', NULL, 'Almond, Grano, Number One', NULL, '2025-05-11 12:43:30'),
	('b71ffb3e-6ef4-4596-9569-990f4349cdec', 421, '73600420-046b-47f4-b9fe-c8ff10dbec0f', NULL, 'OSW Dorota', NULL, '2025-06-01 07:33:06'),
	('763a287f-df5e-4cc9-add2-6be7d12d36c9', 51, '8bee90f4-bb54-4a0e-b302-bce11e79ed82', NULL, 'Focus Sopot', NULL, '2025-06-01 07:43:02');


--
-- Data for Name: messenger_messages; Type: TABLE DATA; Schema: public; Owner: -
--



--
-- Data for Name: service; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public.service (id, name, description, price) VALUES
	('f7c4f469-078d-4914-94ca-ac90c40f8893', 'Odpalanie kable', NULL, '50 zł'),
	('2f2765fc-23c2-4007-929e-619193493bde', 'Zakupy', NULL, '50 zł'),
	('8d088b2e-a640-4abc-9d3e-43c2f793ae2d', 'Odprowadzanie auta', NULL, 'Licznik x3'),
	('9ed05e7f-e45d-4756-ba35-6e41644d9817', 'Holowanie auta', NULL, '35 zł + licznik'),
	('ae796272-87a9-4761-884c-132e81cdf392', 'Przesyłki', 'Jeśli wskazanie taksometru przekroczy 50 zł -> koszt usługi = wskazanie taksometru', 'min. 50 zł'),
	('5618e929-10fc-4c66-b7f7-639ad4739e9a', 'Gabaryt', 'Przewóz przedmiotów ponadnormatywnych (krzesła, stoliki, większy sprzęt agd np. telewizor) bądź większa ilość zakupów, bagaży (przy złożonych siedzeniach)', '120 zł'),
	('45847101-403e-4ee2-811c-ec286aae8969', 'BUS', 'Do ostatecznej wyceny kursu doliczamy 50%', '+50%'),
	('b0cd5d32-b15d-4d4d-87ab-3d7b247b6b3c', 'Kombi / Fotelik', 'Do ostatecznej wyceny kursu doliczamy 20%', '+20%'),
	('ef6f6736-f10b-4737-97c1-048bd9341287', 'Zwierzęta', NULL, '+20 zł');


--
-- Data for Name: user; Type: TABLE DATA; Schema: public; Owner: -
--

INSERT INTO public."user" (id, email, password, roles) VALUES
	('94dca688-46b1-42c3-8667-3c4a92b914e3', 'biuro@neptuntaxi.pl', '$2y$13$Are.7PJJ8mhVqt.R5wZvRO5mE.dYPAYVBVjBR/yhgA/HamehOm.Qa', '["ROLE_ADMIN"]');


--
-- Name: messenger_messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.messenger_messages_id_seq', 5543, true);


--
-- PostgreSQL database dump complete
--

