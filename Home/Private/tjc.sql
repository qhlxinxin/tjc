SELECT * FROM `school_relative`
LEFT JOIN school ON school_relative.scid=school.scid
LEFT JOIN school_manager ON school.school_name=school_manager.username
LEFT JOIN manager_role_school on manager_role_school.mid=school_manager.mid

-- update mrs
UPDATE manager_role_school as mrs ,
(SELECT * FROM `school_relative`  INNER JOIN school ON school_relative.scid=school.scid
INNER JOIN school_manager ON school.school_name=school_manager.username LEFT JOIN manager_role_school on manager_role_school.mid=school_manager.mid ) as t

INNER JOIN mrs.mid=t.mid

SET mrs.scid=t.scid,mrs.rgid


--
select * from
`school_relative`  INNER JOIN school ON school_relative.scid=school.scid
INNER JOIN school_manager ON school.school_name=school_manager.username LEFT JOIN manager_role_school on manager_role_school.mid=school_manager.mid



UPDATE `school_relative`  INNER JOIN school ON school_relative.scid=school.scid
INNER JOIN school_manager ON school.school_name=school_manager.username LEFT JOIN manager_role_school on manager_role_school.mid=school_manager.mid

SET manager_role_school.scid=school_manager.scid



--
select school_relative.*,
school.scid as school_scid,
school.level,
school.school_name,
school.status scool_status,
school_manager.*,
manager_role_school.mrsid,
manager_role_school.mid as mrs_mid,
manager_role_school.scid as mrs_scid,
manager_role_school.rgid
 from `school_relative` INNER JOIN school ON school_relative.scid=school.scid INNER JOIN school_manager ON school.school_name=school_manager.username LEFT JOIN manager_role_school on manager_role_school.mrsid=school.scid

--  没有 -
UPDATE
`school_relative`  INNER JOIN school ON school_relative.scid=school.scid
INNER JOIN school_manager ON school.school_name=school_manager.username LEFT JOIN manager_role_school on manager_role_school.mrsid=school.scid

SET manager_role_school.mid=school_manager.mid,manager_role_school.scid=school.scid

--  有 -
UPDATE
`school_relative`  INNER JOIN school ON school_relative.scid=school.scid
INNER JOIN school_manager ON SUBSTRING(school.school_name,4)=school_manager.username LEFT JOIN manager_role_school on manager_role_school.mrsid=school.scid

SET manager_role_school.mid=school_manager.mid,manager_role_school.scid=school.scid


--
SELECT * FROM (
SELECT
school_relative.*,
school.scid as school_scid,
school.level,
school.school_name,
school.status scool_status,
school_manager.*,
manager_role_school.mrsid,
manager_role_school.mid as mrs_mid,
manager_role_school.scid as mrs_scid,
manager_role_school.rgid
    FROM `school_relative`
LEFT JOIN school ON school_relative.scid=school.scid
LEFT JOIN school_manager ON school.school_name=school_manager.username
LEFT JOIN manager_role_school on manager_role_school.mid=school_manager.mid
 ) t



-- 最终改进版  基础
select school_relative.*,
school.scid as school_scid,
school.level,
school.school_name,
school.status scool_status,
school_manager.*,
manager_role_school.mrsid,
manager_role_school.mid as mrs_mid,
manager_role_school.scid as mrs_scid,
manager_role_school.rgid
 from `school_relative` INNER JOIN school ON school_relative.scid=school.scid
 INNER JOIN school_manager ON substring(school.school_name,4)=school_manager.username LEFT JOIN manager_role_school on manager_role_school.mrsid=school.scid