import { Card } from 'primereact/card';
import React from 'react';

interface FactorioVersionFilterProps {
    versions: string[];
    selectedVersion: string;
    onVersionChange: (value: string) => void;
}

export const FactorioVersionFilter: React.FC<FactorioVersionFilterProps> = ({
    versions,
    selectedVersion,
    onVersionChange,
}) => {
    return (
        <Card
            style={{
                border: '1px solid #374151',
                background: 'rgba(31,41,55,0.6)',
                backdropFilter: 'blur(8px)',
                borderRadius: '16px',
                padding: '1rem',
            }}
        >
            <h3
                style={{
                    margin: '0 0 1rem 0',
                    color: '#e5e7eb',
                    fontSize: '1.1rem',
                }}
            >
                <i
                    className="pi pi-server"
                    style={{ marginRight: '0.5rem' }}
                />
                Factorio Version
            </h3>
            <div
                style={{
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '0.5rem',
                }}
            >
                <div
                    onClick={() => onVersionChange('')}
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: '0.5rem',
                        padding: '0.5rem 0.75rem',
                        borderRadius: '8px',
                        cursor: 'pointer',
                        backgroundColor:
                            selectedVersion === ''
                                ? 'rgba(107,114,128,0.2)'
                                : 'transparent',
                        border: `1px solid ${selectedVersion === '' ? '#6b7280' : '#374151'}`,
                        transition: 'all 0.2s',
                    }}
                    className="hover:bg-gray-700/30"
                >
                    <i
                        className="pi pi-list"
                        style={{
                            color:
                                selectedVersion === ''
                                    ? '#6b7280'
                                    : '#6b7280',
                            fontSize: '0.9rem',
                        }}
                    />
                    <span
                        style={{ color: '#e5e7eb', fontSize: '0.9rem' }}
                    >
                        All
                    </span>
                </div>
                {versions.map((version) => {
                    const active = selectedVersion === version;

                    return (
                        <div
                            key={version}
                            onClick={() => onVersionChange(version)}
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: '0.5rem',
                                padding: '0.5rem 0.75rem',
                                borderRadius: '8px',
                                cursor: 'pointer',
                                backgroundColor: active
                                    ? 'rgba(6,182,212,0.15)'
                                    : 'transparent',
                                border: `1px solid ${active ? '#06b6d4' : '#374151'}`,
                                transition: 'all 0.2s',
                            }}
                            className="hover:bg-gray-700/30"
                        >
                            <i
                                className="pi pi-tag"
                                style={{
                                    color: active ? '#06b6d4' : '#6b7280',
                                    fontSize: '0.9rem',
                                }}
                            />
                            <span
                                style={{ color: '#e5e7eb', fontSize: '0.9rem' }}
                            >
                                {version}
                            </span>
                        </div>
                    );
                })}
            </div>
        </Card>
    );
};
