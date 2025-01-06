import useSWR from 'swr';
import { fetcher } from '../functions';

const useApi = ( api, defaultValue ) => {
	const { data, error } = useSWR( api, fetcher, {
		dedupingInterval: 60 * 60 * 1000, // Cache requests for 1 hour.
	} );

	return error || !data ? defaultValue : data;
};

export default useApi;