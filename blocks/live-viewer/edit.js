import { useBlockProps } from '@wordpress/block-editor';
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default function Edit() {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<Placeholder
				icon="video-alt"
				label={ __( 'Live Viewer', 'events-calendar-live-viewer' ) }
				instructions={ __(
					'The live viewer is not previewed in the editor. When you visit this page the live viewer will show the next live event video.',
					'events-calendar-live-viewer'
				) }
			/>
		</div>
	);
}
