-- on dsp DB
update dsp_reflection_results set code = 'ENG 114' where id in (1,2);
update dsp_reflection_results set code = 'ENG 104' where id in (4,5);
update dsp_reflection_results set code = 'ENG 114 or ENG 104' where id in (3,6);