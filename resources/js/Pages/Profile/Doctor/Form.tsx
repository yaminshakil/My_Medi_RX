import InputError from '@/Components/input-error';
import LogoCropper from '@/Components/LogoCropper';
import SpecialtiesSelect from '@/Components/SpecialtiesSelect';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { LoaderCircle } from 'lucide-react';

export default function Form({ data, setData, errors, handleSubmit, processing, initialImage, setShowConfirm, is_delete, submitTitle, specialties }) {
    return (
        <form role="form" onSubmit={handleSubmit} className="w-full p-4 shadow-md sm:rounded-lg">
            <div className="grid w-full grid-cols-1 gap-4 md:grid-cols-3">
                <h1 className="col-span-1 text-left text-xl font-extrabold md:col-span-3">Profile</h1>
                {/* Left: Profile image */}
                <div className="rounded border p-4">
                    <Label>Profile Image</Label>
                    <LogoCropper data={data} setData={setData} field="profile_image" initialImage={initialImage} />
                    <InputError message={errors.profile_image} />
                </div>

                {/* Middle: Basic info */}
                <div className="flex flex-col gap-4 rounded border p-4">
                    <div>
                        <Label htmlFor="phone">Phone</Label>
                        <Input
                            id="phone"
                            type="text"
                            value={data.phone || ''}
                            onChange={(e) => setData('phone', e.target.value)}
                            disabled={processing}
                            placeholder="Phone"
                        />
                        <InputError message={errors.phone} className="mt-2" />
                    </div>

                    <div>
                        <Label htmlFor="dob">Date of Birth</Label>
                        <Input id="dob" type="date" value={data.dob || ''} onChange={(e) => setData('dob', e.target.value)} disabled={processing} />
                        <InputError message={errors.dob} className="mt-2" />
                    </div>

                    <div>
                        <Label htmlFor="gender">Gender</Label>
                        <select
                            id="gender"
                            value={data.gender || ''}
                            onChange={(e) => setData('gender', e.target.value)}
                            disabled={processing}
                            className="w-full rounded-md border p-2"
                        >
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                        <InputError message={errors.gender} className="mt-2" />
                    </div>

                    <div>
                        <Label htmlFor="specialization">Specialization</Label>
                        <Input
                            id="specialization"
                            type="text"
                            value={data.specialization || ''}
                            onChange={(e) => setData('specialization', e.target.value)}
                            disabled={processing}
                            placeholder="e.g. Cardiology"
                        />
                        <InputError message={errors.specialization} className="mt-2" />
                    </div>

                    <div>
                        <Label htmlFor="speciality" className="after:text-red-500 after:content-['*']">
                            Specialties
                        </Label>

                        <SpecialtiesSelect
                            groups={specialties} // your grouped specialties array
                            value={data.specialization_ids || []} // selected child ids
                            onChange={(val) => setData('specialization_ids', val)}
                        />

                        {errors.specialization_ids && <InputError className="mt-2 text-red-700" message={errors.specialization_ids} />}
                    </div>

                    <div>
                        <Label htmlFor="working_institute">Working Institute</Label>
                        <Input
                            id="working_institute"
                            type="text"
                            value={data.working_institute || ''}
                            onChange={(e) => setData('working_institute', e.target.value)}
                            disabled={processing}
                            placeholder="e.g. Abc Medical Colledge and Hospital"
                        />
                        <InputError message={errors.working_institute} className="mt-2" />
                    </div>

                    <div>
                        <Label htmlFor="designation">Designation</Label>
                        <Input
                            id="designation"
                            type="text"
                            value={data.designation || ''}
                            onChange={(e) => setData('designation', e.target.value)}
                            disabled={processing}
                            placeholder="e.g. Consultant"
                        />
                        <InputError message={errors.designation} className="mt-2" />
                    </div>
                </div>

                {/* Right: Doctor info */}
                <div className="flex flex-col gap-4 rounded border p-4">
                    <div>
                        <Label htmlFor="qualification">Qualification</Label>
                        <Input
                            id="qualification"
                            type="text"
                            value={data.qualification || ''}
                            onChange={(e) => setData('qualification', e.target.value)}
                            disabled={processing}
                            placeholder="e.g. MBBS, FCPS"
                        />
                        <InputError message={errors.qualification} className="mt-2" />
                    </div>

                    <div>
                        <Label htmlFor="registration_no">Registration No</Label>
                        <Input
                            id="registration_no"
                            type="text"
                            value={data.registration_no || ''}
                            onChange={(e) => setData('registration_no', e.target.value)}
                            disabled={processing}
                            placeholder="e.g. 542242"
                        />
                        <InputError message={errors.registration_no} className="mt-2" />
                    </div>

                    <div>
                        <Label htmlFor="experience_years">Experience (Years)</Label>
                        <Input
                            id="experience_years"
                            type="number"
                            value={data.experience_years || ''}
                            onChange={(e) => setData('experience_years', parseInt(e.target.value) || '')}
                            disabled={processing}
                            placeholder="Years of experience"
                        />
                        <InputError message={errors.experience_years} className="mt-2" />
                    </div>

                    <div>
                        <Label htmlFor="facebook">Facebook</Label>
                        <Input
                            id="facebook"
                            placeholder="Facebook URL"
                            disabled={processing}
                            value={data.social.facebook || ''}
                            onChange={(e) =>
                                setData('social', {
                                    ...data.social,
                                    facebook: e.target.value,
                                })
                            }
                        />
                        <InputError message={errors['social.facebook']} className="mt-2" />
                    </div>

                    <div>
                        <Label htmlFor="linkedin">Linkedin</Label>
                        <Input
                            id="linkedin"
                            placeholder="Linkedin URL"
                            disabled={processing}
                            value={data.social.linkedin || ''}
                            onChange={(e) =>
                                setData('social', {
                                    ...data.social,
                                    linkedin: e.target.value,
                                })
                            }
                        />
                        <InputError message={errors['social.linkedin']} className="mt-2" />
                    </div>

                    <div>
                        <Label htmlFor="twitter">Twitter</Label>
                        <Input
                            id="twitter"
                            placeholder="Twitter URL"
                            disabled={processing}
                            value={data.social.twitter || ''}
                            onChange={(e) =>
                                setData('social', {
                                    ...data.social,
                                    twitter: e.target.value,
                                })
                            }
                        />
                        <InputError message={errors['social.twitter']} className="mt-2" />
                    </div>

                    <div className="flex items-center gap-1">
                        <Input
                            className="w-6"
                            id="active"
                            type="checkbox"
                            checked={data.active}
                            onChange={(e) => setData('active', e.target.checked)}
                            disabled={processing}
                        />
                        <Label htmlFor="active">Active</Label>
                    </div>
                </div>
            </div>

            <div className="mt-4 w-full">
                <Label htmlFor="bio">Bio</Label>
                <Textarea
                    id="bio"
                    value={data.bio || ''}
                    onChange={(e) => setData('bio', e.target.value)}
                    disabled={processing}
                    placeholder="Short introduction"
                />
                <InputError message={errors.bio} className="mt-2" />
            </div>

            <div className="mt-4 flex justify-start gap-2">
                <Button type="submit" className="mt-2 w-fit" disabled={processing}>
                    {processing && <LoaderCircle className="mr-2 h-4 w-4 animate-spin" />}
                    {submitTitle}
                </Button>
                {is_delete && (
                    <Button
                        variant="destructive"
                        type="button"
                        onClick={() => {
                            setShowConfirm(true);
                        }}
                        className="mt-2 w-fit"
                    >
                        Delete
                    </Button>
                )}
            </div>
        </form>
    );
}
