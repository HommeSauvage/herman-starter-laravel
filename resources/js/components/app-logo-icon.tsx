import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path
                fillRule="evenodd"
                clipRule="evenodd"
                d="M12 1.5 22.5 12 12 22.5 1.5 12 12 1.5Zm0 6L16.5 12 12 16.5 7.5 12 12 7.5Z"
            />
        </svg>
    );
}
