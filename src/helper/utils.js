import { useState, useEffect } from "@wordpress/element";

export function useDebounce(value, wait = 100) {
	const [debouncedValue, setDebouncedValue] = useState(value);
	useEffect(() => {
		const handler = setTimeout(() => {
			setDebouncedValue(value);
		}, wait);
		return () => {
			clearTimeout(handler);
		};
	}, [value]);
	return debouncedValue;
}

export default {
	useDebounce
};
