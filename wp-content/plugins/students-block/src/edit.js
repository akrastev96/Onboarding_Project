import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, ToggleControl, Spinner } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';
import { useState, useEffect } from '@wordpress/element';

export default function Edit( { attributes, setAttributes } ) {
	const { numberOfStudents, filterByStatus, showSingle, studentId } = attributes;
	const blockProps = useBlockProps();

	// Fetch students for the dropdown.
	const students = useSelect( ( select ) => {
		return select( 'core' ).getEntityRecords( 'postType', 'student', {
			per_page: 100,
			status: 'publish',
		} );
	}, [] );

	// Update studentId when showSingle changes to false.
	useEffect( () => {
		if ( ! showSingle && studentId > 0 ) {
			setAttributes( { studentId: 0 } );
		}
	}, [ showSingle ] );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Student Settings', 'students-block' ) } initialOpen={ true }>
					<ToggleControl
						label={ __( 'Show Single Student', 'students-block' ) }
						checked={ showSingle }
						onChange={ ( value ) => setAttributes( { showSingle: value } ) }
					/>

					{ showSingle && (
						<SelectControl
							label={ __( 'Select Student', 'students-block' ) }
							value={ studentId }
							options={ [
								{ label: __( '-- Select Student --', 'students-block' ), value: 0 },
								...( students
									? students.map( ( student ) => ( {
											label: student.title?.rendered || `Student #${ student.id }`,
											value: student.id,
									  } ) )
									: [] ),
							] }
							onChange={ ( value ) => setAttributes( { studentId: parseInt( value, 10 ) } ) }
						/>
					) }

					{ ! showSingle && (
						<RangeControl
							label={ __( 'Number of Students', 'students-block' ) }
							value={ numberOfStudents }
							onChange={ ( value ) => setAttributes( { numberOfStudents: value } ) }
							min={ 1 }
							max={ 20 }
						/>
					) }

					<SelectControl
						label={ __( 'Filter by Status', 'students-block' ) }
						value={ filterByStatus }
						options={ [
							{ label: __( 'All Students', 'students-block' ), value: 'all' },
							{ label: __( 'Active Only', 'students-block' ), value: 'active' },
							{ label: __( 'Inactive Only', 'students-block' ), value: 'inactive' },
						] }
						onChange={ ( value ) => setAttributes( { filterByStatus: value } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				{ students === null ? (
					<div style={ { padding: '20px', textAlign: 'center' } }>
						<Spinner />
						<p>{ __( 'Loading students...', 'students-block' ) }</p>
					</div>
				) : (
					<ServerSideRender
						block="students-block/students"
						attributes={ attributes }
					/>
				) }
			</div>
		</>
	);
}

