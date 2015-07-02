#include <stdio.h>
#include <stdlib.h>
#include <openjpeg.h>
#include "lib/opj_res.h"

int main(int argc, char** argv) {
	struct opj_res resources;
	int read_status = READ_FAILURE;
	opj_dparameters_t parameters;

	if(argc < 2) { return 1; }
	opj_set_default_decoder_parameters(&parameters);
	resources = opj_init(argv[1], &parameters); 

	if(resources.status == 0) {
		opj_codestream_info_v2_t* info = opj_get_cstr_info(resources.l_codec);
		printf("{\"x1\":%d,\"y1\":%d, \"tw\": %d, \"th\": %d, \"tdx\": %d, \"tdy\": %d, \"num_res\": %d, \"num_comps\": %d}", 
			resources.image->x1, 
			resources.image->y1,
			info->tw,
			info->th,
			info->tdx,
			info->tdy,
			info->m_default_tile_info.tccp_info[0].numresolutions,
			resources.image->numcomps
		);
		opj_destroy_cstr_info(&info);
		read_status = READ_SUCCESS;
	}

	opj_cleanup(&resources);

	return read_status;
}