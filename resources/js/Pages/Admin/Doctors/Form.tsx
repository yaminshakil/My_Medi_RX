import FormFooter from '@/components/Form/FormFooter';
import InputError from '@/components/input-error';
import LogoCropper from '@/components/LogoCropper';
import SpecialtiesSelect from '@/components/SpecialtiesSelect';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';

export default function Form({
    data,
    setData,
    errors,
    processing,
    specialties,
    handleSubmit,
    handleCancel,
    submitBtnTitle,
    heading,
    isUpdate = false,
    initialImage,
}) {
    return (
        <form role="form" className="w-full border p-4 shadow-md sm:rounded-lg" onSubmit={handleSubmit}>
            <div className="grid w-full grid-cols-1 gap-6 md:grid-cols-3">
                <h1 className="col-span-1 mb-4 text-left text-xl font-extrabold md:col-span-3">{heading}</h1>

                {/* Left Column - Profile Image */}
                <div className="flex flex-col items-center gap-4 rounded-lg border p-4">
                    <Label htmlFor="profile_image">Profile Image</Label>
                    <LogoCropper data={data} setData={setData} field="profile_image" initialImage={initialImage} />
                    <InputError message={errors.profile_image} className="mt-2" />
                </div>

                {/* Middle Column - Basic Info */}
                <div className="flex flex-col gap-4 rounded-lg border p-4">
                    <div className="grid gap-2">
                        <Label htmlFor="name" className="after:text-red-500 after:content-['*']">
                            Name
                        </Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            disabled={processing}
                            placeholder="Name"
                        />
                        <InputError message={errors.name} className="mt-2" />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email" className="after:text-red-500 after:content-['*']">
                            Email
                        </Label>
                        <Input
                            id="email"
                            type="email"
                            required
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            disabled={processing}
                            placeholder="Email"
                        />
                        <InputError message={errors.email} className="mt-2" />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password" className={!isUpdate && "after:text-red-500 after:content-['*']"}>
                            Password {!isUpdate && '(leave blank if not changing)'}
                        </Label>
                        <Input
                            id="password"
                            type="password"
                            required={!isUpdate}
                            value={data.password}
                            onChange={(e) => setData('password', e.target.value)}
                            disabled={processing}
                            placeholder="Password"
                        />
                        <InputError message={errors.password} className="mt-2" />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation" className={!isUpdate && "after:text-red-500 after:content-['*']"}>
                            Confirm Password
                        </Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            required={!isUpdate}
                            value={data.password_confirmation}
                            onChange={(e) => setData('password_confirmation', e.target.value)}
                            disabled={processing}
                            placeholder="Confirm password"
                        />
                        <InputError message={errors.password_confirmation} />
                    </div>

                    <div className="grid gap-2">
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

                    <div className="grid gap-2">
                        <Label htmlFor="dob">Date of Birth</Label>
                        <Input id="dob" type="date" value={data.dob || ''} onChange={(e) => setData('dob', e.target.value)} disabled={processing} />
                        <InputError message={errors.dob} className="mt-2" />
                    </div>
                    <div className="grid gap-2">
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

                    <div className="grid gap-2">
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

                    <div className="grid gap-2">
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
                </div>

                {/* Right Column - Professional Info */}
                <div className="flex flex-col gap-4 rounded-lg border p-4">
                    <div className="grid gap-2">
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

                    <div className="grid gap-2">
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

                    <div className="grid gap-2">
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

                    <div className="grid gap-2">
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

                    <div className="grid gap-2">
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

                    <div className="grid gap-2">
                        <Label htmlFor="facebook">Facebook</Label>
                        <Input
                            placeholder="Facebook URL"
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

                    <div className="grid gap-2">
                        <Label htmlFor="linkedin">Linkedin</Label>
                        <Input
                            placeholder="Linkedin URL"
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

                    <div className="grid gap-2">
                        <Label htmlFor="twitter">Twitter</Label>
                        <Input
                            placeholder="Twitter URL"
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
                    <div className="flex items-center gap-1">
                        <Input
                            className="w-6"
                            type="checkbox"
                            id="featured"
                            checked={data.featured}
                            onChange={(e) => setData('featured', e.target.checked)}
                            disabled={processing}
                        />
                        <Label htmlFor="featured">Featured Doctor</Label>
                    </div>
                </div>
            </div>
            <div className="mt-4 grid w-full gap-2">
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
                <FormFooter handleCancel={handleCancel} processing={processing} submitTitle={submitBtnTitle} />
            </div>
        </form>
    );
}
