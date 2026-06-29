import { Button } from 'primereact/button';
import { Dialog } from 'primereact/dialog';
import type { DropdownChangeEvent } from 'primereact/dropdown';
import { Dropdown } from 'primereact/dropdown';
import { InputText } from 'primereact/inputtext';
import { Message } from 'primereact/message';
import { ProgressSpinner } from 'primereact/progressspinner';
import React, { useEffect } from 'react';
import { useAuditForm } from '@/hooks/useAuditForm';

interface AuditDialogProps {
    visible: boolean;
    onHide: () => void;
}

export const AuditDialog: React.FC<AuditDialogProps> = ({
    visible,
    onHide,
}) => {
    const {
        searchQuery,
        searchResults,
        selectedMod,
        versions,
        selectedVersion,
        loading,
        submitting,
        result,
        error,
        searchMods,
        selectMod,
        setVersion,
        submit,
        reset,
    } = useAuditForm();

    useEffect(() => {
        if (!visible) {
            reset();
        }
    }, [visible, reset]);

    const handleSearch = (e: React.ChangeEvent<HTMLInputElement>) => {
        searchMods(e.target.value);
    };

    const handleVersionChange = (e: DropdownChangeEvent) => {
        setVersion(e.value);
    };

    const handleKeyDown = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter' && selectedMod && !submitting) {
            submit();
        }
    };

    const footer = (
        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '0.5rem' }}>
            <Button
                label="Cancel"
                severity="secondary"
                outlined
                onClick={onHide}
                disabled={submitting}
            />
            <Button
                label={submitting ? 'Queuing...' : 'Audit'}
                icon={submitting ? 'pi pi-spin pi-spinner' : 'pi pi-play'}
                onClick={submit}
                disabled={!selectedMod || submitting}
            />
        </div>
    );

    return (
        <Dialog
            header="Audit Mod"
            visible={visible}
            onHide={onHide}
            footer={footer}
            style={{ width: '500px' }}
            modal
            closable={!submitting}
        >
            <div
                style={{
                    display: 'flex',
                    flexDirection: 'column',
                    gap: '1rem',
                }}
            >
                {/* Search input */}
                <div>
                    <label
                        style={{
                            display: 'block',
                            marginBottom: '0.5rem',
                            color: '#e5e7eb',
                            fontWeight: '500',
                        }}
                    >
                        Search mod
                    </label>
                    <div style={{ position: 'relative' }}>
                        <InputText
                            value={searchQuery}
                            onChange={handleSearch}
                            onKeyDown={handleKeyDown}
                            placeholder="Type mod name..."
                            disabled={submitting}
                            style={{ width: '100%' }}
                        />
                        {loading && (
                            <div
                                style={{
                                    position: 'absolute',
                                    right: '0.75rem',
                                    top: '50%',
                                    transform: 'translateY(-50%)',
                                }}
                            >
                                <ProgressSpinner
                                    style={{ width: '1.2rem', height: '1.2rem' }}
                                    strokeWidth="4"
                                />
                            </div>
                        )}
                    </div>
                </div>

                {/* Search results */}
                {searchResults.length > 0 && !selectedMod && (
                    <div
                        style={{
                            border: '1px solid #374151',
                            borderRadius: '8px',
                            maxHeight: '200px',
                            overflowY: 'auto',
                        }}
                    >
                        {searchResults.map((mod) => (
                            <div
                                key={mod.id}
                                onClick={() => selectMod(mod)}
                                style={{
                                    padding: '0.5rem 0.75rem',
                                    cursor: 'pointer',
                                    borderBottom: '1px solid #374151',
                                    color: '#e5e7eb',
                                }}
                                className="hover:bg-gray-700/50"
                            >
                                <div style={{ fontWeight: '500' }}>
                                    {mod.name}
                                </div>
                                <div
                                    style={{
                                        fontSize: '0.8rem',
                                        color: '#9ca3af',
                                    }}
                                >
                                    {mod.title}
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {/* Selected mod */}
                {selectedMod && (
                    <div
                        style={{
                            padding: '0.75rem',
                            background: 'rgba(6,182,212,0.1)',
                            border: '1px solid #06b6d4',
                            borderRadius: '8px',
                        }}
                    >
                        <div style={{ color: '#06b6d4', fontWeight: '500' }}>
                            {selectedMod.name}
                        </div>
                        <div
                            style={{
                                fontSize: '0.8rem',
                                color: '#9ca3af',
                            }}
                        >
                            {selectedMod.title}
                        </div>
                    </div>
                )}

                {/* Version selector */}
                {selectedMod && versions.length > 0 && (
                    <div>
                        <label
                            style={{
                                display: 'block',
                                marginBottom: '0.5rem',
                                color: '#e5e7eb',
                                fontWeight: '500',
                            }}
                        >
                            Version
                        </label>
                        <Dropdown
                            value={selectedVersion}
                            options={versions.map((v) => ({
                                label: `${v.version} (Factorio ${v.factorio_version})`,
                                value: v.version,
                            }))}
                            onChange={handleVersionChange}
                            style={{ width: '100%' }}
                            disabled={submitting}
                        />
                    </div>
                )}

                {/* Success message */}
                {result && (
                    <Message
                        severity="success"
                        text={result.message}
                        style={{ width: '100%' }}
                    />
                )}

                {/* Error message */}
                {error && (
                    <Message
                        severity="error"
                        text={error}
                        style={{ width: '100%' }}
                    />
                )}
            </div>
        </Dialog>
    );
};
